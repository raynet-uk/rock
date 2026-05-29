<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleEventsSeeder extends Seeder
{
    /**
     * Seed the database with clearly-marked SAMPLE events.
     *
     * Note to future me:
     * - Safe to run multiple times: it deletes any previous sample events first.
     * - Real events (is_sample = false) are never touched.
     */
    public function run(): void
    {
        // 1) Make sure I have some event types to hang these on.
        // If real ones already exist, I’ll reuse them.
        if (EventType::count() === 0) {
            EventType::insert([
                [
                    'name'       => 'Training Net',
                    'slug'       => 'training-net',
                    'colour'     => '#38bdf8',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name'       => 'Public Event',
                    'slug'       => 'public-event',
                    'colour'     => '#22c55e',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name'       => 'Exercise',
                    'slug'       => 'exercise',
                    'colour'     => '#f97316',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name'       => 'Duty / Standby',
                    'slug'       => 'duty-standby',
                    'colour'     => '#a855f7',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        $types = EventType::all()->keyBy('slug');

        // 2) Nuke any previous SAMPLE EVENT DATA so this seeder is repeatable.
        Event::where('is_sample', true)->delete();

        // 3) Build a small library of sample definitions.
        $baseDate = Carbon::today()->next('monday'); // start from next Monday

        $samples = [
            [
                'title'    => 'SAMPLE – Weekly Training Net',
                'slug'     => 'sample-weekly-training-net',
                'type'     => $types['training-net'] ?? $types->first(),
                'dow'      => 'monday',
                'time'     => '20:00',
                'duration' => 60,
                'location' => 'City-wide (VHF)',
                'description' => 'SAMPLE EVENT DATA – routine Monday training net. Used for testing the website layout and calendar.',
            ],
            [
                'title'    => 'SAMPLE – Monthly Exercise: Riverfront',
                'slug'     => 'sample-monthly-exercise-riverfront',
                'type'     => $types['exercise'] ?? $types->first(),
                'dow'      => 'saturday',
                'time'     => '10:00',
                'duration' => 180,
                'location' => 'Town Centre',
                'description' => 'SAMPLE EVENT DATA – field exercise by the waterfront.',
            ],
            [
                'title'    => 'SAMPLE – Public Event: 10k Road Race',
                'slug'     => 'sample-public-event-10k-road-race',
                'type'     => $types['public-event'] ?? $types->first(),
                'dow'      => 'sunday',
                'time'     => '09:30',
                'duration' => 210,
                'location' => 'Sefton Park',
                'description' => 'SAMPLE EVENT DATA – typical charity road race deployment.',
            ],
            [
                'title'    => 'SAMPLE – Duty / Standby for WX Alert',
                'slug'     => 'sample-duty-standby-wx-alert',
                'type'     => $types['duty-standby'] ?? $types->first(),
                'dow'      => 'friday',
                'time'     => '18:00',
                'duration' => 120,
                'location' => 'Home QTH (remote monitoring)',
                'description' => 'SAMPLE EVENT DATA – group on standby for severe weather.',
            ],
        ];

        // 4) Actually generate ~20 events by cloning these patterns forward in time.
        $eventsToInsert = [];
        $eventCount     = 0;

        foreach (range(0, 7) as $weekOffset) { // 8 weeks ahead
            foreach ($samples as $sample) {
                if ($eventCount >= 20) {
                    break 2; // bail out of both loops once we hit 20
                }

                $date = $baseDate->copy()
                    ->addWeeks($weekOffset)
                    ->next($sample['dow']); // move to the requested weekday

                $start = Carbon::parse($date->format('Y-m-d') . ' ' . $sample['time']);
                $end   = $start->copy()->addMinutes($sample['duration']);

                $eventsToInsert[] = [
                    'title'       => $sample['title'] . ' (Week +' . $weekOffset . ')',
                    'slug'        => Str::slug($sample['slug'] . '-w' . $weekOffset . '-' . ($eventCount + 1)),
                    'event_type_id' => $sample['type']->id,
                    'starts_at'   => $start,
                    'ends_at'     => $end,
                    'location'    => $sample['location'],
                    'description' => $sample['description'],
                    'is_sample'   => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];

                $eventCount++;
            }
        }

        Event::insert($eventsToInsert);
    }
}