<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ControllerDashboardController extends Controller
{
    private function gate(): void
    {
        $user = auth()->user();
        $allowed = ['Group Controller', 'Deputy Controller'];
        abort_unless($user && ($user->is_admin || in_array($user->operator_title, $allowed)), 403);
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────
    public function index()
    {
        $this->gate();
        $tiers = $this->currentTiers();
        $activeAlert = DB::table('controller_alerts')->where('status','active')->latest('raised_at')->first();
        $recentAlerts = DB::table('controller_alerts')->orderByDesc('raised_at')->limit(5)->get();
        $yearStats = $this->yearStats();
        return view('admin.controller.index', compact('tiers','activeAlert','recentAlerts','yearStats'));
    }

    // ── Tiers ─────────────────────────────────────────────────────────────────
    public function tiers()
    {
        $this->gate();
        $current = DB::table('controller_tiers')
            ->join('users','users.id','=','controller_tiers.user_id')
            ->whereNull('controller_tiers.end_date')
            ->orWhere('controller_tiers.end_date','>=',now())
            ->select('controller_tiers.*','users.name','users.callsign')
            ->orderBy('controller_tiers.tier')->orderBy('users.name')
            ->get();
        $history = DB::table('controller_tiers')
            ->join('users','users.id','=','controller_tiers.user_id')
            ->where('controller_tiers.end_date','<',now())
            ->select('controller_tiers.*','users.name','users.callsign')
            ->orderByDesc('controller_tiers.start_date')->limit(50)->get();
        $members = User::orderBy('name')->get(['id','name','callsign']);
        return view('admin.controller.tiers', compact('current','history','members'));
    }

    public function storeTier(Request $request)
    {
        $this->gate();
        $request->validate([
            'user_id'    => ['required','exists:users,id'],
            'tier'       => ['required','in:tier1,tier2,tier3,support,standby'],
            'start_date' => ['required','date'],
            'end_date'   => ['nullable','date','after:start_date'],
            'notes'      => ['nullable','string','max:255'],
        ]);
        DB::table('controller_tiers')->insert([
            'user_id'    => $request->user_id,
            'tier'       => $request->tier,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date ?: null,
            'notes'      => $request->notes,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return back()->with('success','Member added to tier.');
    }

    public function deleteTier($id)
    {
        $this->gate();
        DB::table('controller_tiers')->where('id',$id)->delete();
        return back()->with('success','Tier assignment removed.');
    }

    // ── Alerts ────────────────────────────────────────────────────────────────
    public function alerts()
    {
        $this->gate();
        $activeAlert = DB::table('controller_alerts')->where('status','active')->latest('raised_at')->first();
        $responses = $activeAlert ? DB::table('controller_alert_responses')
            ->join('users','users.id','=','controller_alert_responses.user_id')
            ->where('alert_id',$activeAlert->id)
            ->select('controller_alert_responses.*','users.name','users.callsign')
            ->orderBy('controller_alert_responses.tier')
            ->orderByDesc('controller_alert_responses.responded_at')
            ->get() : collect();
        $history = DB::table('controller_alerts')->where('status','closed')->orderByDesc('raised_at')->limit(20)->get();
        return view('admin.controller.alerts', compact('activeAlert','responses','history'));
    }

    public function raiseAlert(Request $request)
    {
        $this->gate();
        $request->validate([
            'type'       => ['required','in:test,standby,callout'],
            'title'      => ['nullable','string','max:255'],
            'message'    => ['nullable','string'],
            'tier_scope' => ['nullable','array'],
        ]);

        // Close any existing active alert
        DB::table('controller_alerts')->where('status','active')->update(['status'=>'closed','closed_at'=>now()]);

        $statusMap = ['test'=>4,'standby'=>2,'callout'=>1];
        $alertId = DB::table('controller_alerts')->insertGetId([
            'type'             => $request->type,
            'title'            => $request->title ?: ucfirst($request->type).' Alert',
            'message'          => $request->message,
            'tier_scope'       => json_encode($request->tier_scope ?: ['tier1','tier2','tier3','support','standby']),
            'raised_by'        => auth()->id(),
            'raised_at'        => now(),
            'status'           => 'active',
            'status_level_set' => $statusMap[$request->type] ?? null,
            'created_at'       => now(), 'updated_at' => now(),
        ]);

        // Set group status level
        if (isset($statusMap[$request->type])) {
            Setting::set('alert_status_level', $statusMap[$request->type]);
        }

        // Get affected members from active tier assignments
        $scope = $request->tier_scope ?: ['tier1','tier2','tier3','support','standby'];
        $members = DB::table('controller_tiers')
            ->join('users','users.id','=','controller_tiers.user_id')
            ->whereIn('controller_tiers.tier', $scope)
            ->where(function($q){ $q->whereNull('end_date')->orWhere('end_date','>=',now()); })
            ->where('controller_tiers.start_date','<=',now())
            ->select('users.id','users.name','users.email','users.callsign','controller_tiers.tier')
            ->get();

        // Create response records and send notifications
        foreach ($members as $member) {
            $token = Str::random(32);
            DB::table('controller_alert_responses')->insert([
                'alert_id'   => $alertId,
                'user_id'    => $member->id,
                'response'   => 'no_response',
                'tier'       => $member->tier,
                'token'      => $token,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            // Send email notification
            try {
                $respondUrl = url('/alert-respond/'.$token);
                $groupName  = Setting::get('group_name', config('app.name'));
                $subject    = "🚨 {$groupName} — ".ucfirst($request->type)." Alert";
                $body       = $request->message ?: "A {$request->type} alert has been raised by {$groupName}.";
                Mail::raw(
                    "{$subject}\n\n{$body}\n\nPlease respond:\nAvailable: {$respondUrl}?r=available\nUnavailable: {$respondUrl}?r=unavailable\n\nThis alert was raised at ".now()->format('H:i d M Y'),
                    function($m) use ($member, $subject) {
                        $m->to($member->email, $member->name)->subject($subject);
                    }
                );
            } catch (\Throwable $e) {}
        }

        return redirect()->route('admin.controller.alerts')->with('success', count($members).' members alerted.');
    }

    public function closeAlert($id)
    {
        $this->gate();
        DB::table('controller_alerts')->where('id',$id)->update(['status'=>'closed','closed_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','Alert closed.');
    }

    public function markResponse(Request $request, $id)
    {
        $this->gate();
        DB::table('controller_alert_responses')->where('id',$id)->update([
            'response'     => $request->response,
            'responded_at' => $request->response !== 'no_response' ? now() : null,
            'updated_at'   => now(),
        ]);
        return response()->json(['success'=>true]);
    }

    // ── Self-respond via token (no login required) ────────────────────────────
    public function tokenRespond($token, Request $request)
    {
        $row = DB::table('controller_alert_responses')->where('token',$token)->first();
        if (!$row) abort(404);
        $response = in_array($request->r, ['available','unavailable']) ? $request->r : 'available';
        DB::table('controller_alert_responses')->where('token',$token)->update([
            'response'     => $response,
            'responded_at' => now(),
            'updated_at'   => now(),
        ]);
        return view('controller.responded', ['response'=>$response]);
    }

    // ── Alert summary ─────────────────────────────────────────────────────────
    public function alertSummary($id)
    {
        $this->gate();
        $alert = DB::table('controller_alerts')->where('id',$id)->firstOrFail();
        $responses = DB::table('controller_alert_responses')
            ->join('users','users.id','=','controller_alert_responses.user_id')
            ->where('alert_id',$id)
            ->select('controller_alert_responses.*','users.name','users.callsign')
            ->get();
        $tiers = ['tier1'=>'Tier 1','tier2'=>'Tier 2','tier3'=>'Tier 3','support'=>'Support','standby'=>'Standby'];
        $summary = [];
        foreach ($tiers as $key => $label) {
            $tierResponses = $responses->where('tier',$key);
            $total = $tierResponses->count();
            if (!$total) continue;
            $available = $tierResponses->where('response','available');
            $times = $available->whereNotNull('responded_at')->map(function($r) use ($alert) {
                return Carbon::parse($r->responded_at)->diffInMinutes(Carbon::parse($alert->raised_at));
            });
            $summary[$key] = [
                'label'    => $label,
                'total'    => $total,
                'available'=> $available->count(),
                'pct'      => $total ? round($available->count()/$total*100) : 0,
                'avg_min'  => $times->count() ? round($times->avg()) : null,
                'callsigns'=> $available->pluck('callsign')->filter()->values(),
            ];
        }
        return view('admin.controller.alert-summary', compact('alert','summary','responses'));
    }

    // ── Annual Return ─────────────────────────────────────────────────────────
    public function annualReturn(Request $request)
    {
        $this->gate();
        $year = $request->input('year', now()->year);
        $stats = $this->yearStats($year);
        return view('admin.controller.annual-return', compact('stats','year'));
    }

    private function yearStats(int $year = null): array
    {
        $year = $year ?? now()->year;
        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end   = Carbon::create($year, 12, 31)->endOfDay();

        return [
            'year'             => $year,
            'member_count'     => User::whereNotIn('status',['Inactive','Suspended'])->whereNull('guest_expires_at')->whereDoesntHave('roles', function($q){ $q->whereIn('name',['temporary_guest','temporary_admin','test_user']); })->count(),
            'user_service_events' => DB::table('events')->whereBetween('starts_at',[$start,$end])->where('event_type_id', function($q){ $q->select('id')->from('event_types')->where('name','like','%user service%')->limit(1); })->count(),
            'exercises'        => DB::table('events')->whereBetween('starts_at',[$start,$end])->where('event_type_id', function($q){ $q->select('id')->from('event_types')->where('name','like','%exercise%')->limit(1); })->count(),
            'live_callouts'    => DB::table('controller_alerts')->where('type','callout')->whereBetween('raised_at',[$start,$end])->count(),
            'test_alerts'      => DB::table('controller_alerts')->where('type','test')->whereBetween('raised_at',[$start,$end])->count(),
            'standby_alerts'   => DB::table('controller_alerts')->where('type','standby')->whereBetween('raised_at',[$start,$end])->count(),
            'total_events'     => DB::table('events')->whereBetween('starts_at',[$start,$end])->count(),
        ];
    }

    private function currentTiers(): array
    {
        $rows = DB::table('controller_tiers')
            ->join('users','users.id','=','controller_tiers.user_id')
            ->where('controller_tiers.start_date','<=',now())
            ->where(function($q){ $q->whereNull('controller_tiers.end_date')->orWhere('controller_tiers.end_date','>=',now()); })
            ->select('controller_tiers.*','users.name','users.callsign')
            ->orderBy('controller_tiers.tier')->orderBy('users.name')
            ->get();
        $grouped = ['tier1'=>[],'tier2'=>[],'tier3'=>[],'support'=>[],'standby'=>[]];
        foreach ($rows as $r) $grouped[$r->tier][] = $r;
        return $grouped;
    }
}
