<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ModuleManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Exception;

class ModuleController extends Controller
{
    public function __construct(protected ModuleManager $modules) {}

    // ── Module Manager page ───────────────────────────────────────────────

    public function index()
    {
        $modules = $this->modules->all();
        $updates = $this->modules->checkUpdates();

        foreach ($updates as $alias => $info) {
            if (isset($modules[$alias])) {
                $modules[$alias]['update'] = $info;
            }
        }

        return view('admin.modules.index', [
            'modules'     => $modules,
            'updateCount' => count($updates),
            'coreVersion' => config('raynet_modules.core_version'),
        ]);
    }

    // ── Enable ────────────────────────────────────────────────────────────

    public function enable(string $alias)
    {
        try {
            $this->modules->enable($alias);
            return back()->with('success', "'{$alias}' activated successfully.");
        } catch (Exception $e) {
            return back()->with('error', "Could not activate '{$alias}': " . $e->getMessage());
        }
    }

    // ── Disable ───────────────────────────────────────────────────────────

    public function disable(string $alias)
    {
        try {
            $this->modules->disable($alias);
            return back()->with('success', "'{$alias}' deactivated.");
        } catch (Exception $e) {
            return back()->with('error', "Could not deactivate '{$alias}': " . $e->getMessage());
        }
    }

    // ── Remote update ─────────────────────────────────────────────────────

    public function update(string $alias)
    {
        try {
            $this->modules->update($alias);
            return back()->with('success', "'{$alias}' updated successfully.");
        } catch (Exception $e) {
            return back()->with('error', "Update failed for '{$alias}': " . $e->getMessage());
        }
    }

    // ── Refresh update cache ──────────────────────────────────────────────

    public function refreshUpdates()
    {
        cache()->forget('raynet_module_updates');
        $this->modules->checkUpdates();
        return back()->with('success', 'Update check complete.');
    }

    // ── ZIP Upload & Install ──────────────────────────────────────────────

    public function upload(Request $request)
    {
        $request->validate([
            'module_zip' => [
                'required',
                'file',
                'mimes:zip',
                'max:20480', // 20 MB
            ],
        ]);

        $zip     = new ZipArchive();
        $tmpPath = $request->file('module_zip')->getRealPath();

        // Open the ZIP
        if ($zip->open($tmpPath) !== true) {
            return back()->with('error', 'Could not open ZIP file. Make sure it is a valid archive.');
        }

        // ── Locate the module.json inside the ZIP ─────────────────────────
        // Support two structures:
        //   ModuleName/module.json   (folder at root)
        //   module.json              (files at root)
        $manifestIndex = $zip->locateName('module.json');
        $moduleFolder  = null;

        if ($manifestIndex === false) {
            // Look one level deep
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (str_ends_with($name, '/module.json') && substr_count($name, '/') === 1) {
                    $manifestIndex = $i;
                    $moduleFolder  = dirname($name); // e.g. "Announcements"
                    break;
                }
            }
        }

        if ($manifestIndex === false) {
            $zip->close();
            return back()->with('error', 'No module.json found in the ZIP. Make sure your module ZIP contains a module.json manifest.');
        }

        // ── Read and validate the manifest ────────────────────────────────
        $manifestJson = $zip->getFromIndex($manifestIndex);
        $manifest     = json_decode($manifestJson, true);

        if (! $manifest || empty($manifest['alias']) || empty($manifest['name'])) {
            $zip->close();
            return back()->with('error', 'module.json is invalid or missing required fields (name, alias).');
        }

        $alias      = preg_replace('/[^a-z0-9_\-]/', '', strtolower($manifest['alias']));
        $moduleName = ucfirst($alias);
        $targetPath = base_path("Modules/{$moduleName}");

        // ── Extract ───────────────────────────────────────────────────────
        File::ensureDirectoryExists(base_path('Modules'));

        if ($moduleFolder) {
            // ZIP has a root folder — extract into Modules/ renaming the folder
            File::deleteDirectory($targetPath);
            File::makeDirectory($targetPath, 0755, true);

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);

                // Only extract files inside our module folder
                if (! str_starts_with($name, $moduleFolder . '/')) {
                    continue;
                }

                $relativePath = substr($name, strlen($moduleFolder) + 1);

                if (empty($relativePath)) {
                    continue; // Skip the directory entry itself
                }

                if (str_ends_with($name, '/')) {
                    // It's a directory
                    File::makeDirectory("{$targetPath}/{$relativePath}", 0755, true, true);
                } else {
                    $dir = dirname("{$targetPath}/{$relativePath}");
                    File::ensureDirectoryExists($dir);
                    file_put_contents("{$targetPath}/{$relativePath}", $zip->getFromIndex($i));
                }
            }
        } else {
            // Files are at ZIP root — extract directly into Modules/ModuleName/
            File::deleteDirectory($targetPath);
            $zip->extractTo($targetPath);
        }

        $zip->close();

        // ── Check providers exist ─────────────────────────────────────────
        // We don't auto-activate — user activates via the list.
        // But we do confirm the manifest is readable from disk now.
        $diskManifest = json_decode(File::get("{$targetPath}/module.json"), true);

        if (! $diskManifest) {
            return back()->with('error', "Module installed but manifest could not be re-read. Check {$targetPath}/module.json.");
        }

        return back()->with('success', "Module '{$manifest['name']}' installed successfully. You can now activate it below.");
    }

    // ── Delete / Uninstall ────────────────────────────────────────────────

    public function delete(string $alias)
    {
        try {
            // Disable first if active
            $this->modules->disable($alias);

            // Remove from DB
            \Illuminate\Support\Facades\DB::table('modules')->where('alias', $alias)->delete();

            // Delete the folder
            $module = $this->modules->get($alias);
            if ($module && File::isDirectory($module['path'])) {
                File::deleteDirectory($module['path']);
            }

            cache()->forget('raynet_modules_enabled');
            return back()->with('success', "Module '{$alias}' deleted.");
        } catch (Exception $e) {
            return back()->with('error', "Could not delete '{$alias}': " . $e->getMessage());
        }
    }

// ── Browse module registry ────────────────────────────────────────────
    public function browseRegistry()
    {
        $registryUrl = config('raynet_modules.registry_url',
            'https://raw.githubusercontent.com/raynet-uk/raynet-cms-modules/main/registry.json');

        $ctx = stream_context_create(['http' => ['timeout' => 8, 'header' => "User-Agent: ROCK-CMS\r\n"]]);
        $json = @file_get_contents($registryUrl, false, $ctx);

        if (!$json) {
            return response()->json(['error' => 'Could not reach the module registry. Check your internet connection.'], 503);
        }

        $registry = json_decode($json, true);
        if (!$registry || empty($registry['modules'])) {
            return response()->json(['error' => 'Registry response was invalid.'], 503);
        }

        // Mark which modules are already installed
        $installed = $this->modules->all();
        foreach ($registry['modules'] as &$mod) {
            $mod['installed'] = isset($installed[$mod['alias']]);
            $mod['enabled']   = $installed[$mod['alias']]['enabled'] ?? false;
        }

        return response()->json($registry);
    }

    // ── Install module from registry URL ─────────────────────────────────
    public function installFromRegistry(Request $request)
    {
        $request->validate([
            'download_url' => ['required', 'url'],
            'alias'        => ['required', 'string', 'max:60'],
        ]);

        $url = $request->input('download_url');

        // Only allow downloads from trusted sources
        $allowed = ['github.com', 'raw.githubusercontent.com', 'objects.githubusercontent.com'];
        $host = parse_url($url, PHP_URL_HOST);
        if (!in_array($host, $allowed)) {
            return response()->json(['error' => 'Download URL must be from github.com.'], 403);
        }

        // Download the ZIP
        $ctx = stream_context_create(['http' => ['timeout' => 30, 'header' => "User-Agent: ROCK-CMS\r\n"]]);
        $zip = @file_get_contents($url, false, $ctx);

        if (!$zip) {
            return response()->json(['error' => 'Could not download module ZIP.'], 503);
        }

        // Save to temp
        $tmpPath = sys_get_temp_dir() . '/rock_module_' . uniqid() . '.zip';
        file_put_contents($tmpPath, $zip);

        // Use existing upload logic by faking a request
        $tmpFile = new \Illuminate\Http\UploadedFile($tmpPath, basename($tmpPath), 'application/zip', null, true);
        $fakeRequest = new Request();
        $fakeRequest->files->set('module_zip', $tmpFile);
        $fakeRequest->setMethod('POST');

        try {
            $result = $this->upload($fakeRequest);
            @unlink($tmpPath);
            $session = $result->getSession();
            if ($session && $session->has('error')) {
                return response()->json(['error' => $session->get('error')], 422);
            }
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            @unlink($tmpPath);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
