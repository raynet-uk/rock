<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class NetSchedule extends Model {
    protected $fillable = [
        'name','callsign','frequency','band','controller','controller_slots',
        'description','announcement','days_of_week','repeat_type','repeat_anchor',
        'start_time','end_time','auto_activate','is_active','priority',
    ];
    protected $casts = [
        'days_of_week'    => 'array',
        'controller_slots'=> 'array',
        'auto_activate'   => 'boolean',
        'is_active'       => 'boolean',
        'repeat_anchor'   => 'date',
    ];

    public static array $bands = [
        'hf'   => ['label'=>'HF',     'colour'=>'#f59e0b','bg'=>'rgba(245,158,11,.15)','border'=>'rgba(245,158,11,.4)'],
        'vhf'  => ['label'=>'VHF',    'colour'=>'#3b82f6','bg'=>'rgba(59,130,246,.15)','border'=>'rgba(59,130,246,.4)'],
        'uhf'  => ['label'=>'UHF',    'colour'=>'#8b5cf6','bg'=>'rgba(139,92,246,.15)','border'=>'rgba(139,92,246,.4)'],
        'shf'  => ['label'=>'SHF',    'colour'=>'#ec4899','bg'=>'rgba(236,72,153,.15)','border'=>'rgba(236,72,153,.4)'],
        'dmr'  => ['label'=>'DMR',    'colour'=>'#10b981','bg'=>'rgba(16,185,129,.15)','border'=>'rgba(16,185,129,.4)'],
        'dstar'=> ['label'=>'D-STAR', 'colour'=>'#06b6d4','bg'=>'rgba(6,182,212,.15)','border'=>'rgba(6,182,212,.4)'],
    ];

    public static array $priorities = [
        'routine'   => ['label'=>'Routine',   'colour'=>'#3b82f6'],
        'urgent'    => ['label'=>'Urgent',    'colour'=>'#f59e0b'],
        'emergency' => ['label'=>'Emergency', 'colour'=>'#C8102E'],
    ];

    public function isScheduledToday(Carbon $now = null): bool {
        $now  = $now ?? Carbon::now()->timezone('Europe/London');
        $days = $this->days_of_week ?? [];
        $dayMap = [0=>'sun',1=>'mon',2=>'tue',3=>'wed',4=>'thu',5=>'fri',6=>'sat'];
        $today = $dayMap[$now->dayOfWeek];
        if (!in_array($today, $days)) return false;
        return match($this->repeat_type) {
            'weekly'      => true,
            'fortnightly' => $this->isFortnightlyWeek($now),
            'monthly'     => $this->isMonthlyOccurrence($now),
            default       => true,
        };
    }

    private function isFortnightlyWeek(Carbon $now): bool {
        $anchor = $this->repeat_anchor ?? $this->created_at->toDateString();
        $anchor = Carbon::parse($anchor)->startOfWeek();
        $weeks  = (int) $anchor->diffInWeeks($now->copy()->startOfWeek());
        return $weeks % 2 === 0;
    }

    private function isMonthlyOccurrence(Carbon $now): bool {
        $anchor = $this->repeat_anchor ?? $this->created_at->toDateString();
        $anchor = Carbon::parse($anchor);
        $anchorWeekOfMonth = (int) ceil($anchor->day / 7);
        $nowWeekOfMonth    = (int) ceil($now->day / 7);
        return $anchorWeekOfMonth === $nowWeekOfMonth;
    }

    public function activeController(Carbon $now = null): string {
        $now   = $now ?? Carbon::now()->timezone('Europe/London');
        $slots = $this->controller_slots ?? [];
        $time  = $now->format('H:i');
        foreach ($slots as $slot) {
            $from = $slot['from'] ?? '00:00';
            $to   = $slot['to']   ?? '23:59';
            if ($time >= $from && $time < $to) {
                return strtoupper($slot['callsign'] ?? '');
            }
        }
        return strtoupper($this->controller ?? '');
    }

    public function isLiveNow(Carbon $now = null): bool {
        $now    = $now ?? Carbon::now()->timezone('Europe/London');
        $dayMap = [0=>'sun',1=>'mon',2=>'tue',3=>'wed',4=>'thu',5=>'fri',6=>'sat'];
        foreach (['today' => $now->copy(), 'yesterday' => $now->copy()->subDay()] as $which => $base) {
            $checkDay = $which === 'today' ? $now : $now->copy()->subDay();
            if (!$this->isScheduledToday($checkDay)) continue;
            $start = Carbon::createFromFormat('H:i:s', $this->start_time, 'Europe/London')->setDate($base->year,$base->month,$base->day);
            $end   = Carbon::createFromFormat('H:i:s', $this->end_time,   'Europe/London')->setDate($base->year,$base->month,$base->day);
            if ($end->lte($start)) $end->addDay();
            if ($now->between($start, $end)) return true;
        }
        return false;
    }

    public function nextOccurrences(int $days = 7): array {
        $now    = Carbon::now()->timezone('Europe/London');
        $result = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $now->copy()->addDays($i);
            if ($this->isScheduledToday($date)) {
                $start = Carbon::createFromFormat('H:i:s', $this->start_time, 'Europe/London')->setDate($date->year,$date->month,$date->day);
                $end   = Carbon::createFromFormat('H:i:s', $this->end_time,   'Europe/London')->setDate($date->year,$date->month,$date->day);
                if ($end->lte($start)) $end->addDay();
                $result[] = ['date'=>$date->format('Y-m-d'),'label'=>$date->format('D j M'),'start'=>$start,'end'=>$end,'schedule'=>$this];
            }
        }
        return $result;
    }
}
