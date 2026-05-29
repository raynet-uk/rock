<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CmsUpdateController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->is_super_admin, 403);
        $localVersion   = trim(file_get_contents(base_path('VERSION')));
        $remoteVersion  = Setting::get('update_remote_version', $localVersion);
        $updateAvailable = Setting::get('update_available', '0') === '1';
        $lastUpdated    = Setting::get('last_updated_at');
        $checkedAt      = Setting::get('update_checked_at');
        return view('admin.cms-update.index', compact('localVersion','remoteVersion','updateAvailable','lastUpdated','checkedAt'));
    }

    public function checkNow()
    {
        abort_unless(auth()->user()?->is_super_admin, 403);
        Artisan::call('cms:check-update');
        return back()->with('status', 'Update check complete.');
    }

    public function applyUpdate()
    {
        abort_unless(auth()->user()?->is_super_admin, 403);
        Artisan::call('cms:update', ['--force' => true]);
        return redirect()->route('admin.cms-update.index')->with('status', Artisan::output());
    }

    public function dismissInterstitial()
    {
        Setting::set('show_update_interstitial', '0');
        return redirect()->route('admin.dashboard');
    }
}
