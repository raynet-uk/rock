<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class ModuleManager
{
    protected string $modulesPath;
    protected string $updateServerUrl;

    public function __construct()
    {
        $this->modulesPath     = base_path('Modules');
        $this->updateServerUrl = config('raynet_modules.update_server_url', '');
    }

    public function all(): array
    {
        $dbModules = DB::table('modules')->get()->keyBy('alias')->toArray();
        $modules   = ['core' => $this->coreEntry()];

        if (! File::isDirectory($this->modulesPath)) {
            return $modules;
        }

        foreach (File::directories($this->modulesPath) as $dir) {
            $manifestPath = $dir . '/module.json';
            if (! File::exists($manifestPath)) continue;

            $m     = json_decode(File::get($manifestPath), true);
            $alias = $m['alias'] ?? strtolower(basename($dir));

            if ($alias === 'core') continue;

            $dbRecord = $dbModules[$alias] ?? null;

            $modules[$alias] = [
                'name'          => $m['name']          ?? $alias,
                'alias'         => $alias,
                'description'   => $m['description']   ?? '',
                'version'       => $m['version']       ?? '0.0.1',
                'requires_core' => $m['requires_core'] ?? '*',
                'providers'     => $m['providers']     ?? [],
                'path'          => $dir,
                'author'        => $m['author']        ?? '',
                'author_uri'    => $m['author_uri']    ?? '',
                'module_uri'    => $m['module_uri']    ?? '',
                'docs_uri'      => $m['docs_uri']      ?? '',
                'license'       => $m['license']       ?? '',
                'license_uri'   => $m['license_uri']   ?? '',
                'tags'          => $m['tags']           ?? [],
                'changelog'     => $m['changelog']     ?? [],
                'components'    => $m['components']    ?? [],
                'is_core'       => false,
                'can_disable'   => true,
                'can_delete'    => true,
                'enabled'       => $dbRecord ? (bool) $dbRecord->enabled : false,
                'installed'     => $dbRecord !== null,
                'installed_at'  => $dbRecord->installed_at ?? null,
                'update'        => null,
            ];
        }

        return $modules;
    }

    public function enabled(): array
    {
        return array_filter($this->all(), fn($m) => $m['enabled']);
    }

    public function get(string $alias): ?array
    {
        return $this->all()[$alias] ?? null;
    }

    protected function coreEntry(): array
    {
        $manifestPath = $this->modulesPath . '/Core/module.json';
        $m = File::exists($manifestPath)
            ? json_decode(File::get($manifestPath), true)
            : [];

        return [
            'name'          => $m['name']        ?? 'RAYNET Core',
            'alias'         => 'core',
            'description'   => $m['description'] ?? 'The foundational system that powers every RAYNET site. Cannot be disabled or removed.',
            'version'       => $m['version']     ?? config('app.raynet.core_version', '1.0.0'),
            'requires_core' => '*',
            'providers'     => $m['providers']   ?? [],
            'path'          => $this->modulesPath . '/Core',
            'author'        => $m['author']      ?? 'RAYNET Liverpool',
            'author_uri'    => $m['author_uri']  ?? 'https://raynet-liverpool.net',
            'module_uri'    => '',
            'docs_uri'      => '',
            'license'       => $m['license']     ?? 'GPL-2.0+',
            'license_uri'   => $m['license_uri'] ?? 'https://www.gnu.org/licenses/gpl-2.0.txt',
            'tags'          => $m['tags']         ?? ['core', 'system'],
            'changelog'     => $m['changelog']   ?? [],
            'components'    => $m['components']  ?? [],
            'is_core'       => true,
            'can_disable'   => false,
            'can_delete'    => false,
            'enabled'       => true,
            'installed'     => true,
            'installed_at'  => null,
            'update'        => null,
        ];
    }

    public function enable(string $alias): bool
    {
        if ($alias === 'core') throw new Exception('The Core module cannot be modified.');

        $module = $this->get($alias);
        if (! $module) throw new Exception("Module '{$alias}' not found on disk.");

        $this->runMigrations($module);

        if (DB::table('modules')->where('alias', $alias)->exists()) {
            DB::table('modules')->where('alias', $alias)->update(['enabled' => true]);
        } else {
            DB::table('modules')->insert([
                'name'         => $module['name'],
                'alias'        => $alias,
                'version'      => $module['version'],
                'enabled'      => true,
                'installed_at' => now(),
                'updated_at'   => now(),
            ]);
        }

        Cache::forget('raynet_modules_enabled');
        return true;
    }

    public function disable(string $alias): bool
    {
        if ($alias === 'core') throw new Exception('The Core module cannot be disabled.');
        DB::table('modules')->where('alias', $alias)->update(['enabled' => false]);
        Cache::forget('raynet_modules_enabled');
        return true;
    }

    public function checkUpdates(): array
    {
        if (empty($this->updateServerUrl)) return [];

        return Cache::remember('raynet_module_updates', 60, function () {
            try {
                $response = Http::timeout(10)
                    ->withOptions(['allow_redirects' => true])
                    ->withHeaders(['User-Agent' => 'RAYNET-CMS/1.0'])
                    ->get($this->updateServerUrl);

                if ($response->failed()) return [];

                $remote  = $response->json('modules', []);
                $updates = [];
                $local   = $this->all();

                foreach ($remote as $alias => $info) {
                    if (isset($local[$alias]) && version_compare($info['version'], $local[$alias]['version'], '>')) {
                        $updates[$alias] = $info;
                    }
                }
                return $updates;
            } catch (Exception $e) {
                return [];
            }
        });
    }

    public function update(string $alias): bool
    {
        if ($alias === 'core') throw new Exception('Core updates are applied via server deployment.');

        $updates = $this->checkUpdates();
        if (! isset($updates[$alias])) throw new Exception("No update available for '{$alias}'.");

        $info    = $updates[$alias];
        $zipPath = storage_path('app/module-updates/' . $alias . '.zip');
        $tmpPath = storage_path('app/module-updates/' . $alias . '_tmp');

        File::ensureDirectoryExists(storage_path('app/module-updates'));

        // Download with redirect following
        $response = Http::timeout(60)
            ->withOptions(['allow_redirects' => true])
            ->withHeaders(['User-Agent' => 'RAYNET-CMS/1.0'])
            ->get($info['download_url']);

        if ($response->failed()) {
            throw new Exception('Failed to download update: HTTP ' . $response->status());
        }

        $body = $response->body();
        if (strlen($body) < 100) {
            throw new Exception('Downloaded file too small (' . strlen($body) . ' bytes) — not a valid ZIP.');
        }

        File::put($zipPath, $body);

        // Open and extract zip
        $zip = new \ZipArchive();
        $opened = $zip->open($zipPath);
        if ($opened !== true) {
            throw new Exception('Failed to open ZIP (error code: ' . $opened . ').');
        }

        // Clean temp dir and extract
        File::deleteDirectory($tmpPath);
        File::makeDirectory($tmpPath, 0755, true);
        $zip->extractTo($tmpPath);
        $zip->close();

        // Find the module folder inside the extracted content
        $dirs = File::directories($tmpPath);
        if (empty($dirs)) {
            throw new Exception('ZIP contained no subdirectories after extraction.');
        }
        $extractedFolder = $dirs[0];
        $moduleName      = basename($extractedFolder);

        // Validate module.json exists in extracted folder
        $newManifestPath = $extractedFolder . '/module.json';
        if (! File::exists($newManifestPath)) {
            throw new Exception('No module.json found in extracted ZIP folder.');
        }
        $newManifest = json_decode(File::get($newManifestPath), true);
        if (! $newManifest) {
            throw new Exception('module.json in ZIP is invalid JSON.');
        }

        // Replace the module on disk
        $targetPath = $this->modulesPath . '/' . $moduleName;
        File::deleteDirectory($targetPath);
        File::copyDirectory($extractedFolder, $targetPath);

        // Cleanup temp files
        File::deleteDirectory($tmpPath);
        File::delete($zipPath);

        // Run any new migrations
        $updated = $this->get($alias);
        if ($updated) {
            $this->runMigrations($updated);
        }

        // Update the DB record
        $newVersion = $newManifest['version'] ?? $info['version'];
        DB::table('modules')->where('alias', $alias)->update([
            'version'    => $newVersion,
            'updated_at' => now(),
        ]);

        Cache::forget('raynet_module_updates');
        Cache::forget('raynet_modules_enabled');
        return true;
    }

    public function boot(): void
    {
        $coreProvider = 'Modules\\Core\\Providers\\CoreServiceProvider';
        if (class_exists($coreProvider)) {
            app()->register($coreProvider);
        }

        try {
            $enabled = Cache::remember('raynet_modules_enabled', 3600, fn() => $this->enabled());
        } catch (Exception $e) {
            return;
        }

        foreach ($enabled as $module) {
            if ($module['alias'] === 'core') continue;
            foreach ($module['providers'] as $providerClass) {
                // Derive the file path directly from the class name so modules
                // work immediately after upload without needing composer dump-autoload.
                // e.g. Modules\AfterActionReport\Providers\AfterActionReportServiceProvider
                //   -> Modules/AfterActionReport/Providers/AfterActionReportServiceProvider.php
                $relative = str_replace(
                    ['Modules\\', '\\'],
                    ['',          '/'],
                    $providerClass
                ) . '.php';

                $full = base_path('Modules/' . $relative);

                if (file_exists($full)) {
                    require_once $full;
                }

                if (class_exists($providerClass)) {
                    app()->register($providerClass);
                }
            }
        }
    }

    protected function runMigrations(array $module): void
    {
        $migrationsPath = $module['path'] . '/Database/Migrations';
        if (File::isDirectory($migrationsPath)) {
            Artisan::call('migrate', [
                '--path'  => str_replace(base_path() . '/', '', $migrationsPath),
                '--force' => true,
            ]);
        }
    }
}