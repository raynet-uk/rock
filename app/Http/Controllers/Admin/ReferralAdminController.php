<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Referral;

class ReferralAdminController extends Controller {
    public function index() {
        $referrals    = Referral::with('referrer')->orderByDesc('sent_at')->get();
        $byReferrer   = $referrals->groupBy('referrer_id');
        $totalReferrals = $referrals->count();
        $referrerCount  = $byReferrer->count();
        $thisMonth      = $referrals->where('sent_at', '>=', now()->startOfMonth())->count();
        return view('admin.referrals.index', compact('referrals','byReferrer','totalReferrals','referrerCount','thisMonth'));
    }

    public function destroy(\App\Models\Referral $referral) {
        $referral->delete();
        return back()->with('success', 'Referral deleted.');
    }
}
