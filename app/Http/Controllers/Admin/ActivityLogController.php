<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $now       = Carbon::now();
        $yearStart = $now->month >= 9
            ? Carbon::create($now->year, 9, 1)
            : Carbon::create($now->year - 1, 9, 1);
        $yearEnd   = $yearStart->copy()->addYear()->subDay();
        $yearLabel = $yearStart->format('M Y') . ' – ' . $yearEnd->format('M Y');

        // ── All-time stats ──
        $allTimeEntries = ActivityLog::count();
        $allTimeHours   = ActivityLog::sum('hours');
        $allTimeUsers   = ActivityLog::distinct('user_id')->count('user_id');

        // ── Academic year stats ──
        $yearQuery       = ActivityLog::whereBetween('event_date', [$yearStart, $yearEnd]);
        $yearHours       = (clone $yearQuery)->sum('hours');
        $yearEntries     = (clone $yearQuery)->count();
        $yearActiveUsers = (clone $yearQuery)->distinct('user_id')->count('user_id');
        $avgHoursPerUser = $yearActiveUsers > 0
            ? number_format($yearHours / $yearActiveUsers, 1)
            : '0.0';

        // ── Monthly chart data ──
        $months         = [];
        $monthlyHours   = [];
        $monthlyEntries = [];
        $cursor = $yearStart->copy();
        while ($cursor->lte($yearEnd) && $cursor->lte($now)) {
            $months[]         = $cursor->format('M y');
            $ms               = $cursor->copy()->startOfMonth();
            $me               = $cursor->copy()->endOfMonth();
            $monthlyHours[]   = round((clone $yearQuery)->whereBetween('event_date', [$ms, $me])->sum('hours'), 1);
            $monthlyEntries[] = (clone $yearQuery)->whereBetween('event_date', [$ms, $me])->count();
            $cursor->addMonth();
        }

        // ── Monthly avg hours per entry ──
        $monthlyAvgHours = [];
        foreach ($months as $i => $m) {
            $e = $monthlyEntries[$i] ?? 0;
            $h = $monthlyHours[$i]   ?? 0;
            $monthlyAvgHours[] = $e > 0 ? round($h / $e, 2) : 0;
        }

        // ── Top events by hours ──
        $topEvents = ActivityLog::select(
                'event_name',
                DB::raw('SUM(hours) as total_hours'),
                DB::raw('COUNT(*) as entry_count')
            )
            ->groupBy('event_name')
            ->orderByDesc('total_hours')
            ->limit(10)
            ->get();

        // ── Per-user breakdown ──
        $perUserRaw = (clone $yearQuery)
            ->select(
                'user_id',
                DB::raw('SUM(hours) as hours'),
                DB::raw('COUNT(*) as entries'),
                DB::raw('COUNT(DISTINCT event_name) as events'),
                DB::raw('MAX(event_date) as last')
            )
            ->groupBy('user_id')
            ->orderByDesc('hours')
            ->get();

        $userMap = User::whereIn('id', $perUserRaw->pluck('user_id'))
            ->pluck('name', 'id');

        $perUserStats = $perUserRaw->map(fn($r) => [
            'name'    => $userMap[$r->user_id] ?? 'Unknown',
            'hours'   => round($r->hours, 1),
            'entries' => $r->entries,
            'events'  => $r->events,
            'last'    => $r->last,
            'user_id' => $r->user_id,
        ]);

        $chartUserNames = $perUserStats->take(10)->pluck('name')->values();
        $chartUserHours = $perUserStats->take(10)->pluck('hours')->values();

        // ── Last year comparison ──
        $lastYearStart   = $yearStart->copy()->subYear();
        $lastYearEnd     = $yearEnd->copy()->subYear();
        $lastYearHours   = ActivityLog::whereBetween('event_date', [$lastYearStart, $lastYearEnd])->sum('hours');
        $lastYearEntries = ActivityLog::whereBetween('event_date', [$lastYearStart, $lastYearEnd])->count();

        $monthlyLastYear = [];
        $cursorLY = $lastYearStart->copy();
        while ($cursorLY->lte($lastYearEnd)) {
            $ms = $cursorLY->copy()->startOfMonth();
            $me = $cursorLY->copy()->endOfMonth();
            $monthlyLastYear[] = round(ActivityLog::whereBetween('event_date', [$ms, $me])->sum('hours'), 1);
            $cursorLY->addMonth();
        }

        // ── Cumulative hours ──
        $cumulativeHours = [];
        $running = 0;
        foreach ($monthlyHours as $h) {
            $running += $h;
            $cumulativeHours[] = round($running, 1);
        }

        // ── Day of week ──
        $dowRaw     = ActivityLog::selectRaw('DAYOFWEEK(event_date) as dow, COUNT(*) as entries, SUM(hours) as hours')
            ->groupBy('dow')->orderBy('dow')->get()->keyBy('dow');
        $dowLabels  = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        $dowEntries = [];
        $dowHours   = [];
        for ($d = 1; $d <= 7; $d++) {
            $dowEntries[] = (int)  ($dowRaw[$d]->entries ?? 0);
            $dowHours[]   = round(  $dowRaw[$d]->hours   ?? 0, 1);
        }

        // ── Same-day multi-member activity ──
        $sameDayActivity = ActivityLog::selectRaw(
                'event_date, COUNT(DISTINCT user_id) as member_count,
                 COUNT(*) as entries, SUM(hours) as total_hours'
            )
            ->groupBy('event_date')
            ->having('member_count', '>', 1)
            ->orderByDesc('member_count')
            ->orderByDesc('total_hours')
            ->limit(12)
            ->get();

        // ── Busiest single day ──
        $busiestDay = ActivityLog::selectRaw('event_date, COUNT(*) as entries, SUM(hours) as hours')
            ->groupBy('event_date')
            ->orderByDesc('hours')
            ->first();

        // ── Top month all time ──
        $topMonth = ActivityLog::selectRaw(
                "DATE_FORMAT(event_date,'%b %Y') as month_label, SUM(hours) as hours, COUNT(*) as entries"
            )
            ->groupBy('month_label')
            ->orderByDesc('hours')
            ->first();

        // ── Member streaks (months active this year) ──
        $totalMonthsSoFar = count($months);
        $memberStreaks     = [];
        foreach ($perUserRaw as $r) {
            $active = 0;
            $cur    = $yearStart->copy();
            while ($cur->lte($yearEnd) && $cur->lte($now)) {
                if (ActivityLog::where('user_id', $r->user_id)
                    ->whereBetween('event_date', [$cur->copy()->startOfMonth(), $cur->copy()->endOfMonth()])
                    ->exists()) {
                    $active++;
                }
                $cur->addMonth();
            }
            $memberStreaks[] = [
                'name'   => $userMap[$r->user_id] ?? 'Unknown',
                'months' => $active,
                'hours'  => round($r->hours, 1),
                'events' => $r->events,
            ];
        }
        usort($memberStreaks, fn($a, $b) => $b['months'] <=> $a['months']);
        $memberStreaks = collect($memberStreaks);

        // ── Heatmap (top 8 members × each month) ──
        $heatmapMembers = $perUserStats->take(8)->pluck('name')->values();
        $heatmapMonths  = collect($months);
        $heatmapGrid    = collect();
        foreach ($heatmapMembers as $mName) {
            $uid     = $perUserStats->firstWhere('name', $mName)['user_id'] ?? null;
            $rowData = [];
            $cur = $yearStart->copy();
            while ($cur->lte($yearEnd) && $cur->lte($now)) {
                $h = $uid
                    ? round(ActivityLog::where('user_id', $uid)
                        ->whereBetween('event_date', [$cur->copy()->startOfMonth(), $cur->copy()->endOfMonth()])
                        ->sum('hours'), 1)
                    : 0;
                $rowData[] = $h;
                $cur->addMonth();
            }
            $heatmapGrid->push(['name' => $mName, 'data' => $rowData]);
        }

        // ── Filtered log entries ──
        $query = ActivityLog::with(['user', 'loggedByUser'])->orderByDesc('event_date');

        if ($request->filled('user_id'))    $query->where('user_id', $request->user_id);
        if ($request->filled('event_name')) $query->where('event_name', 'like', '%' . $request->event_name . '%');
        if ($request->filled('from'))       $query->whereDate('event_date', '>=', $request->from);
        if ($request->filled('to'))         $query->whereDate('event_date', '<=', $request->to);

        $logs         = $query->paginate(25)->withQueryString();
        $totalEntries = $logs->total();
        $totalHours   = ActivityLog::when($request->filled('user_id'),    fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('event_name'), fn($q) => $q->where('event_name', 'like', '%' . $request->event_name . '%'))
            ->when($request->filled('from'),       fn($q) => $q->whereDate('event_date', '>=', $request->from))
            ->when($request->filled('to'),         fn($q) => $q->whereDate('event_date', '<=', $request->to))
            ->sum('hours');

        $users = User::orderBy('name')->select('id', 'name')->get();

        return view('admin.activity-logs.index', compact(
            'yearLabel', 'yearStart', 'yearEnd', 'now',
            'allTimeEntries', 'allTimeHours', 'allTimeUsers',
            'yearHours', 'yearEntries', 'yearActiveUsers', 'avgHoursPerUser',
            'months', 'monthlyHours', 'monthlyEntries', 'monthlyAvgHours',
            'topEvents', 'perUserStats', 'chartUserNames', 'chartUserHours',
            'lastYearHours', 'lastYearEntries', 'monthlyLastYear',
            'cumulativeHours',
            'dowLabels', 'dowEntries', 'dowHours',
            'sameDayActivity', 'busiestDay', 'topMonth',
            'memberStreaks', 'heatmapMembers', 'heatmapMonths', 'heatmapGrid',
            'totalMonthsSoFar',
            'logs', 'totalEntries', 'totalHours',
            'users'
        ));
    }

public function create()
{
    $users  = User::orderBy('name')->select('id', 'name')->get();
    $now    = Carbon::now();
    $acYear = $now->month >= 9
        ? $now->year . '/' . ($now->year + 1)
        : ($now->year - 1) . '/' . $now->year;

    $events = \App\Models\Event::orderBy('starts_at', 'desc')
        ->select('id', 'title', 'starts_at')
        ->get();

    return view('admin.activity-logs.create', compact('users', 'acYear', 'events'));
}

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'    => ['required', 'exists:users,id'],
            'event_name' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'hours'      => ['required', 'numeric', 'min:0.5', 'max:24'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        $data['logged_by'] = auth()->id();

        ActivityLog::create($data);

        $this->syncUserSnapshot($data['user_id']);

        return redirect()->route('admin.activity-logs.index')
            ->with('success', 'Activity log entry created.');
    }


    public function storeBulk(Request $request)
    {
        $data = $request->validate([
            'user_ids'   => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'exists:users,id'],
            'event_name' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'hours'      => ['required', 'numeric', 'min:0.5', 'max:24'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ($data['user_ids'] as $userId) {
            ActivityLog::create([
                'user_id'    => $userId,
                'event_name' => $data['event_name'],
                'event_date' => $data['event_date'],
                'hours'      => $data['hours'],
                'notes'      => $data['notes'] ?? null,
                'logged_by'  => auth()->id(),
            ]);
            $this->syncUserSnapshot($userId);
        }

        $count = count($data['user_ids']);
        return redirect()->route('admin.activity-logs.index')
            ->with('success', "Activity log created for {$count} member(s).");
    }

    public function edit(ActivityLog $activityLog)
    {
        $users = User::orderBy('name')->select('id', 'name')->get();
        return view('admin.activity-logs.edit', compact('activityLog', 'users'));
    }

    public function update(Request $request, ActivityLog $activityLog)
    {
        $data = $request->validate([
            'user_id'    => ['required', 'exists:users,id'],
            'event_name' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'hours'      => ['required', 'numeric', 'min:0.5', 'max:24'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        $oldUserId = $activityLog->user_id;
        $activityLog->update($data);

        $this->syncUserSnapshot($data['user_id']);
        if ($oldUserId !== (int) $data['user_id']) {
            $this->syncUserSnapshot($oldUserId);
        }

        return redirect()->route('admin.activity-logs.index')
            ->with('success', 'Activity log entry updated.');
    }

    public function destroy(ActivityLog $activityLog)
    {
        $userId = $activityLog->user_id;
        $activityLog->delete();
        $this->syncUserSnapshot($userId);

        return redirect()->route('admin.activity-logs.index')
            ->with('success', 'Activity log entry deleted.');
    }

    private function syncUserSnapshot(int $userId): void
    {
        $now       = Carbon::now();
        $yearStart = $now->month >= 9
            ? Carbon::create($now->year, 9, 1)
            : Carbon::create($now->year - 1, 9, 1);
        $yearEnd   = $yearStart->copy()->addYear()->subDay();

        $yearLogs = ActivityLog::where('user_id', $userId)
            ->whereBetween('event_date', [$yearStart, $yearEnd]);

        User::where('id', $userId)->update([
            'events_attended_this_year'    => (clone $yearLogs)->count(),
            'volunteering_hours_this_year' => round((clone $yearLogs)->sum('hours'), 1),
            'attended_event_this_year'     => (clone $yearLogs)->exists(),
        ]);
    }
}