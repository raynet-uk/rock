<?php
namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class CheckUpdateInterstitial
{
    public function handle(Request $request, Closure $next)
    {
        // Only check for authenticated admins on admin routes, skip the dismiss route itself
        if (
            auth()->check() &&
            auth()->user()->is_admin &&
            $request->routeIs('admin.*') &&
            !$request->routeIs('admin.cms-update.dismiss') &&
            Setting::get('show_update_interstitial', '0') === '1'
        ) {
            return response()->view('admin.cms-update.interstitial');
        }

        return $next($request);
    }
}
