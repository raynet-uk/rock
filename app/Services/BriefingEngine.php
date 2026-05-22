<?php
namespace App\Services;

class BriefingEngine {

    private static array $triggers = [
        'brief'     => 'briefing',
        'briefed'   => 'briefing',
        'know'      => 'briefing',
        'understand'=> 'briefing',
        'procedure' => 'checklist_controller',
        'confirm'   => 'checklist_controller',
        'monitor'   => 'checklist_welfare',
        'check'     => 'checklist_technical',
        'inspect'   => 'checklist_technical',
        'assess'    => 'checklist_technical',
        'review'    => 'checklist_controller',
        'practised' => 'briefing',
    ];

    public static function generate(array $risks, array $pack = []): array {
        $briefings  = [];
        $checklists = [];
        $hazards    = array_map(fn($r) => strtolower(is_array($r) ? ($r['hazard']??'') : ($r->hazard??'')), $risks);

        $hasHazard  = fn($needle) => (bool) array_filter($hazards, fn($h) => str_contains($h, $needle));
        $hasControl = function($needle) use ($risks): bool {
            foreach ($risks as $r) {
                $c = strtolower(is_array($r) ? ($r['controls']??'') : ($r->controls??''));
                if (str_contains($c, $needle)) return true;
            }
            return false;
        };

        $freq  = $pack['call_round_interval'] ?? 'every 30 minutes';
        $freq1 = $pack['primary_frequency']   ?? 'TBC';
        $freq2 = $pack['secondary_frequency'] ?? 'TBC';
        $fb    = $pack['fallback_methods']     ?? 'mobile phone';
        $ec    = $pack['event_controller']     ?? 'Event Controller';

        // ── OPERATOR BRIEF 01 — Weather and Exposure ──
        if ($hasHazard('weather') || $hasHazard('heat') || $hasHazard('cold')) {
            $briefings[] = [
                'type'     => 'briefing',
                'ref'      => 'OPERATOR BRIEF 01',
                'title'    => 'Weather and Exposure',
                'trigger'  => 'Weather Exposure / Cold Weather detected.',
                'audience' => 'All operators',
                'body'     => 'You are responsible for arriving prepared for the expected weather.',
                'checklist_items' => [
                    'Waterproof outer layer',
                    'Warm layer available',
                    'Suitable footwear',
                    'Drinking water',
                    'Charged phone',
                ],
                'if_section' => [
                    'label' => 'If you begin feeling:',
                    'items' => ['Cold','Wet','Unwell','Overheated','Fatigued'],
                ],
                'instructions' => [
                    'Inform Control. Do not wait.',
                    'Control may redeploy or withdraw operators.',
                    'Remote or exposed posts may be reduced or closed if conditions deteriorate.',
                ],
                'confirm' => ['Brief received'],
            ];
        }

        // ── OPERATOR BRIEF 02 — Public Access to Control Area ──
        if ($hasHazard('public access') || $hasHazard('control area') || $hasControl('access management')) {
            $briefings[] = [
                'type'     => 'briefing',
                'ref'      => 'OPERATOR BRIEF 02',
                'title'    => 'Public Access to Control Area',
                'trigger'  => 'Operators briefed on access management.',
                'audience' => 'Control staff',
                'body'     => 'Control is a working area. Only authorised personnel enter.',
                'if_section' => [
                    'label' => 'If approached:',
                    'items' => ['Welcome politely.','Determine purpose.','Redirect visitors where possible.'],
                ],
                'do_not' => [
                    'Public handling equipment',
                    'Viewing logs',
                    'Listening to traffic',
                ],
                'escalation' => 'If challenged — escalate to '.$ec.'.',
                'confirm'    => ['Access arrangements understood'],
            ];
        }

        // ── OPERATOR BRIEF 03 — Road Crossings ──
        if ($hasHazard('road') || $hasHazard('traffic') || $hasControl('crossing')) {
            $briefings[] = [
                'type'     => 'briefing',
                'ref'      => 'OPERATOR BRIEF 03',
                'title'    => 'Road Crossings',
                'trigger'  => 'Crossing procedure brief required.',
                'audience' => 'Roadside operators',
                'body'     => 'Roads are not part of the event. Use designated crossing points.',
                'do_not'   => [
                    'Direct traffic',
                    'Step into carriageway',
                    'Stand in blind spots',
                    'Cross alone',
                ],
                'instructions' => [
                    'Use only designated safe crossing points.',
                    'Vehicles: park legally and avoid creating hazards.',
                    'Escalate unsafe traffic situations to Control immediately.',
                ],
                'confirm' => ['Understood'],
            ];
        }

        // ── OPERATOR BRIEF 04 — Safeguarding ──
        if ($hasHazard('under-18') || $hasHazard('safeguard') || $hasHazard('vulnerable')) {
            $briefings[] = [
                'type'     => 'briefing',
                'ref'      => 'OPERATOR BRIEF 04',
                'title'    => 'Safeguarding',
                'trigger'  => 'Under-18 and vulnerable person controls detected.',
                'audience' => 'All operators',
                'body'     => 'You are not acting as carers or supervisors.',
                'do_not'   => [
                    'Work alone with under-18s',
                    'Transport children or vulnerable persons',
                    'Photograph children',
                    'Share personal contact details',
                    'Act as a guardian or carer',
                ],
                'instructions' => [
                    'Any safeguarding concern must be reported to '.$ec.' immediately.',
                    'Do not manage the situation yourself.',
                    'Refer all welfare concerns to the event welfare team.',
                    'DBS checks confirmed for all supervising adults.',
                ],
                'confirm' => ['Safeguarding responsibilities understood','Reporting procedure known'],
            ];
        }

        // ── OPERATOR BRIEF 05 — Lone Working ──
        if ($hasHazard('lone working') || $hasHazard('lone')) {
            $briefings[] = [
                'type'     => 'briefing',
                'ref'      => 'OPERATOR BRIEF 05',
                'title'    => 'Lone Working',
                'trigger'  => 'Welfare monitoring and escalation procedure detected.',
                'audience' => 'All operators working alone',
                'body'     => 'You are responsible for maintaining contact with Control when working alone.',
                'checklist_items' => [
                    'Confirm check-in schedule: '.$freq,
                    'Confirm your location at each check-in',
                    'Emergency contact number confirmed with Group Controller',
                ],
                'instructions' => [
                    'If you miss a check-in — attempt contact immediately.',
                    'Do not move from your post without notifying Control.',
                    'If you feel unsafe — withdraw and contact Control.',
                ],
                'escalation' => 'Two missed check-ins → Control deploys support. Notify '.$ec.' immediately.',
                'confirm'    => ['Check-in schedule understood','Emergency contact confirmed'],
            ];
        }

        // ── OPERATOR BRIEF 06 — Communications Failure ──
        if ($hasHazard('communication') || $hasHazard('comms')) {
            $briefings[] = [
                'type'     => 'briefing',
                'ref'      => 'OPERATOR BRIEF 06',
                'title'    => 'Communications Failure',
                'trigger'  => 'Fallback procedure and comms failure controls detected.',
                'audience' => 'All operators',
                'body'     => 'Loss of communications creates uncertainty and may compromise safety.',
                'checklist_items' => [
                    'Primary frequency: '.$freq1,
                    'Secondary frequency: '.$freq2,
                    'Fallback: '.$fb,
                ],
                'instructions' => [
                    '1. Attempt contact twice on primary.',
                    '2. Change to secondary channel.',
                    '3. Retry twice.',
                    '4. Move to fallback method.',
                    '5. Inform Control of situation.',
                    'Remain in place unless unsafe to do so.',
                ],
                'escalation' => 'Operator → Control → '.$ec,
                'confirm'    => ['Frequencies programmed and checked','Fallback procedure understood'],
            ];
        }

        // ── TECHNICAL SETUP CHECKLIST 01 — Antenna and Mast ──
        if ($hasHazard('mast') || $hasHazard('antenna')) {
            $checklists[] = [
                'type'    => 'checklist_technical',
                'ref'     => 'TECHNICAL SETUP CHECKLIST 01',
                'title'   => 'Antenna and Mast Erection',
                'trigger' => 'Dynamic assessment / erection controls detected.',
                'audience'=> 'Technical operators',
                'sections'=> [
                    'Before erection' => [
                        'Weather suitable',
                        'Overhead hazards checked',
                        'Equipment inspected',
                        'Guy ropes available',
                    ],
                    'During erection' => [
                        'Gloves worn',
                        'Exclusion area maintained',
                        'Mast controlled at all times',
                    ],
                    'After erection' => [
                        'Pegs visible and marked',
                        'Antenna stable',
                        'Feedline secure',
                    ],
                ],
                'stop_if' => ['Wind unsafe','Equipment damaged','Area becomes crowded'],
                'sign_off'=> ['Technical Lead'],
            ];
        }

        // ── TECHNICAL SETUP CHECKLIST 02 — Temporary Power ──
        if ($hasHazard('electrical') || $hasHazard('cable') || $hasHazard('power') || $hasHazard('trip hazard')) {
            $checklists[] = [
                'type'    => 'checklist_technical',
                'ref'     => 'TECHNICAL SETUP CHECKLIST 02',
                'title'   => 'Temporary Power',
                'trigger' => 'Inspection and competent-person controls detected.',
                'sections'=> [
                    'Before energising' => [
                        'PAT labels present and in date',
                        'RCD fitted and tested',
                        'Cables inspected for damage',
                        'No standing water in area',
                    ],
                    'During operation' => [
                        'Connections protected from weather',
                        'Public separated from electrical area',
                        'Heat from connections monitored',
                    ],
                    'After event' => [
                        'Power isolated before dismantling',
                        'Equipment checked for damage',
                        'Any faults reported',
                    ],
                ],
                'sign_off'=> ['Technical Lead'],
            ];
        }

        // ── TECHNICAL SETUP CHECKLIST 03 — Generator ──
        if ($hasHazard('generator')) {
            $checklists[] = [
                'type'    => 'checklist_technical',
                'ref'     => 'TECHNICAL SETUP CHECKLIST 03',
                'title'   => 'Generator',
                'trigger' => 'Generator operation procedure detected.',
                'sections'=> [
                    'Before start' => [
                        'Generator location checked — clear of personnel',
                        'Ventilation confirmed adequate',
                        'Fire extinguisher available within 5 metres',
                        'Fuel stored separately in appropriate container',
                        'Carbon monoxide risk communicated to nearby operators',
                    ],
                    'During event' => [
                        'Fuel level checked',
                        'Area around generator remains clear',
                        'No refuelling while running',
                        'Noise level acceptable',
                    ],
                    'Close down' => [
                        'Generator safely shut down',
                        'Power disconnected from all equipment',
                        'Fuel secured for transport',
                        'Generator cooled before storage',
                    ],
                ],
                'sign_off'=> ['Controller name','Time checked'],
            ];
        }

        // ── WELFARE CHECKLIST 01 — Lone Working ──
        if ($hasHazard('lone')) {
            $checklists[] = [
                'type'     => 'checklist_welfare',
                'ref'      => 'WELFARE CHECKLIST 01',
                'title'    => 'Lone Working',
                'trigger'  => 'Welfare monitoring and escalation procedure detected.',
                'frequency'=> $freq,
                'checks'   => [
                    'Contact made',
                    'Operator location confirmed',
                    'Welfare confirmed',
                    'Battery checked',
                ],
                'escalate_if' => [
                    'Two missed checks',
                    'Unexpected movement',
                    'Concern raised',
                ],
                'actions' => [
                    'Attempt contact',
                    'Notify Controller',
                    'Deploy assistance',
                ],
                'sign_off'=> ['Controller'],
            ];
        }

        // ── WELFARE CHECKLIST 02 — Remote Posts ──
        if ($hasHazard('remote')) {
            $checklists[] = [
                'type'     => 'checklist_welfare',
                'ref'      => 'WELFARE CHECKLIST 02',
                'title'    => 'Remote Posts',
                'trigger'  => 'Check-in schedule and emergency access detected.',
                'sections' => [
                    'Before deployment' => [
                        'Grid reference issued',
                        'Access route known',
                        'Mobile coverage checked',
                        'First aid kit confirmed',
                    ],
                    'Routine checks' => [
                        'Hourly welfare check completed',
                        'Position confirmed',
                    ],
                ],
                'escalate_if' => ['Loss of contact','Operator distress'],
                'sign_off'=> ['Controller'],
            ];
        }

        // ── WELFARE CHECKLIST 03 — Weather ──
        if ($hasHazard('weather') || $hasHazard('heat') || $hasHazard('cold')) {
            $checklists[] = [
                'type'     => 'checklist_welfare',
                'ref'      => 'WELFARE CHECKLIST 03',
                'title'    => 'Weather Monitoring',
                'trigger'  => 'Weather monitoring controls detected.',
                'frequency'=> 'Every hour',
                'checks'   => [
                    'Water available at all operator positions',
                    'Shade or shelter available',
                    'Scheduled breaks being taken',
                    'No operator showing signs of heat or cold stress',
                ],
                'escalate_if' => [
                    'Operator shows confusion or disorientation',
                    'Operator reports dizziness, headache or unwell',
                    'Conditions deteriorate significantly',
                    'Weather warning issued',
                ],
                'actions' => [
                    'Move operator to shelter',
                    'Provide water or warm drinks',
                    'Contact emergency services if required',
                    'Consider suspending deployment at affected posts',
                ],
                'sign_off'=> ['Controller'],
            ];
        }

        // ── CONTROLLER CHECKLIST 01 — Deployment Start ──
        $checklists[] = [
            'type'     => 'checklist_controller',
            'ref'      => 'CONTROLLER CHECKLIST 01',
            'title'    => 'Deployment Start',
            'trigger'  => 'Always generated — confirms readiness before operations commence.',
            'sections' => [
                'Pre-deployment' => [
                    'All operators signed on and accounted for',
                    'Primary frequency confirmed: '.$freq1,
                    'Secondary frequency confirmed: '.$freq2,
                    'Fallback methods confirmed: '.$fb,
                    'Welfare arrangements communicated',
                    'Operator briefings delivered',
                    'Emergency procedure reviewed with all operators',
                    'Emergency contact numbers confirmed',
                    'First aid kit location briefed',
                ],
                'Communications check' => [
                    'Radio check completed with all stations',
                    'Call-round schedule set: '.$freq,
                    'Fallback procedures confirmed',
                ],
                'Sign-off' => [
                    'Controller satisfied deployment is ready to proceed',
                ],
            ],
            'sign_off'=> ['Controller name','Time','Signature'],
        ];

        return ['briefings' => $briefings, 'checklists' => $checklists];
    }
}
