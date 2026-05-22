<?php
namespace App\Services;

use App\Models\EventSupportPack;
use App\Services\BriefingEngine;

class EventPackPdfService {
    private EventSupportPack $pack;
    private \FPDF $pdf;

    public function __construct(EventSupportPack $pack) {
        $this->pack = $pack;
    }

    private function u(?string $s): string {
        if (!$s) return '';
        return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $s) ?: '';
    }

    private function header(string $type, string $subtitle = ''): void {
        $p = $this->pdf;
        $p->SetFillColor(0,51,102);
        $p->SetTextColor(255,255,255);
        $p->SetFont('Arial','B',15);
        $p->Cell(0,11,$this->u('Liverpool RAYNET — '.$type),0,1,'C',true);
        $p->SetFont('Arial','',9);
        $p->Cell(0,6,$this->u('10/ME/179 | '.$this->pack->event_name.' | '.($this->pack->event_date?->format('d M Y') ?? '').($subtitle?' | '.$subtitle:'')),0,1,'C',true);
        $p->Ln(3);
    }

    private function ragBar(): void {
        $p = $this->pdf;
        $ragColours = ['green'=>[5,150,105],'amber'=>[245,158,11],'red'=>[220,38,38]];
        $rc = $ragColours[$this->pack->rag_status] ?? [107,127,150];
        $p->SetFillColor($rc[0],$rc[1],$rc[2]);
        $p->SetTextColor(255,255,255);
        $p->SetFont('Arial','B',12);
        $p->Cell(0,9,$this->u('Overall RAG Status: '.$this->pack->ragLabel()),0,1,'C',true);
        $p->SetTextColor(0,0,0);
        $p->Ln(3);
    }

    private function sectionHead(string $title): void {
        $p = $this->pdf;
        $p->SetFillColor(0,51,102);
        $p->SetTextColor(255,255,255);
        $p->SetFont('Arial','B',10);
        $p->Cell(0,7,$this->u($title),0,1,'L',true);
        $p->SetTextColor(0,0,0);
        $p->SetFont('Arial','',9);
    }

    private function row(string $label, string $value, int $i = 0, int $labelW = 55): void {
        $p = $this->pdf;
        $p->SetFillColor($i%2===0?245:255,$i%2===0?247:255,$i%2===0?250:255);
        $p->Cell($labelW,6,$this->u($label),1,0,'L',true);
        $p->Cell(0,6,$this->u($value),1,1,'L',true);
    }

    private function footer(): void {
        $p = $this->pdf;
        $p->Ln(5);
        $p->SetFont('Arial','I',7);
        $p->SetTextColor(150,150,150);
        $p->Cell(0,4,$this->u('Liverpool RAYNET 10/ME/179 | Generated '.date('d M Y H:i').' | This document supports planning and does not replace dynamic risk assessment or the organiser\'s own statutory obligations.'),0,1,'C');
    }

    public function riskAssessment(): string {
        require_once app_path('Libraries/fpdf/fpdf.php');
        $this->pdf = new \FPDF('L','mm','A4');
        $p = $this->pdf;
        $p->SetMargins(12,12,12);
        $p->SetAutoPageBreak(true,15);
        $p->AddPage();

        $this->header('Event Risk Assessment', 'v'.$this->pack->version.' | '.ucfirst($this->pack->status));
        $this->ragBar();

        if ($this->pack->rag_status === 'red') {
            $p->SetFillColor(254,226,226);
            $p->SetFont('Arial','B',9);
            $p->SetTextColor(200,16,46);
            $p->Cell(0,7,$this->u('WARNING: This assessment contains High residual risks. Group Controller review required before this event can be approved.'),1,1,'C',true);
            $p->SetTextColor(0,0,0);
            $p->Ln(2);
        }
        if ($this->pack->status === 'draft' || $this->pack->approved_at === null) {
            $p->SetFillColor(254,243,199);
            $p->SetFont('Arial','B',9);
            $p->SetTextColor(146,64,14);
            $p->Cell(0,6,$this->u('DRAFT — NOT APPROVED. For planning purposes only.'),1,1,'C',true);
            $p->SetTextColor(0,0,0);
            $p->Ln(2);
        }

        // Section A: Event Details
        $this->sectionHead('A. Event Details');
        $rows = [
            ['Event Name',           $this->pack->event_name],
            ['Location',             $this->pack->location ?? '-'],
            ['Town / Area',          $this->pack->town_area ?? '-'],
            ['Date',                 $this->pack->event_date?->format('d M Y') ?? '-'],
            ['Duration',             $this->pack->duration_days.' day(s)'],
            ['Operating Times',      ($this->pack->start_time ?? 'TBC').' — '.($this->pack->finish_time ?? 'TBC')],
            ['Event Type',           $this->pack->event_type ?? '-'],
        ];
        foreach ($rows as $i => $r) $this->row($r[0],$r[1],$i);
        $p->Ln(3);

        // Section B: User Service
        $this->sectionHead('B. User Service');
        $services = $this->pack->services->pluck('service_name')->join(', ');
        $rows2 = [
            ['Organiser / User Service', $this->pack->organiser_name ?? '-'],
            ['User Services',             $services ?: '-'],
            ['Organiser Contact',         $this->pack->organiser_contact ?? '-'],
            ['Organiser Phone',           $this->pack->organiser_phone ?? '-'],
        ];
        foreach ($rows2 as $i => $r) $this->row($r[0],$r[1],$i);
        $p->Ln(3);

        // Section C: RAYNET Role
        $this->sectionHead('C. RAYNET Role and Scope');
        $roles = implode(', ', (array)($this->pack->raynet_roles ?? ['Communications support']));
        $p->SetFillColor(255,255,255);
        $p->MultiCell(0,5,$this->u('Liverpool RAYNET will provide: '.$roles.'.'),1,'L',true);
        $p->Ln(2);
        $p->MultiCell(0,5,$this->u('This assessment relates to Liverpool RAYNET communications support at '.$this->pack->event_name.'. It does not replace the organiser\'s own event risk assessment or statutory health and safety duties.'),1,'L',true);
        $p->Ln(3);

        // Section D: Operating Period and Controller
        $this->sectionHead('D. Operating Period and Controller');
        $rows3 = [
            ['Event Controller',      $this->pack->event_controller ?? 'TBC'],
            ['Controller Callsign',   $this->pack->controller_callsign ? strtoupper($this->pack->controller_callsign) : 'TBC'],
            ['Deputy Controller',     $this->pack->deputy_controller ?? 'TBC'],
            ['Net Control Location',  $this->pack->net_control_location ?? 'TBC'],
        ];
        foreach ($rows3 as $i => $r) $this->row($r[0],$r[1],$i);
        $p->Ln(3);

        // Section E: Communications Plan
        $this->sectionHead('E. Communications Plan');
        $rows4 = [
            ['Primary Frequency',    $this->pack->primary_frequency ?? 'TBC'],
            ['Secondary Frequency',  $this->pack->secondary_frequency ?? 'TBC'],
            ['Talk-Through',         $this->pack->talkthrough_used ?? 'Unknown'],
            ['Repeater Details',     $this->pack->repeater_details ?? '-'],
            ['Call-Round Interval',  $this->pack->call_round_interval ?? 'TBC'],
            ['Fallback Methods',     $this->pack->fallback_methods ?? 'TBC'],
        ];
        foreach ($rows4 as $i => $r) $this->row($r[0],$r[1],$i);
        $p->Ln(3);

        // Section F: Dynamic risk statement
        $this->sectionHead('F. Dynamic Risk Statement');
        $p->SetFillColor(255,255,255);
        $p->MultiCell(0,5,$this->u('Health and safety is a shared responsibility. Situations may arise which could not reasonably have been foreseen. Where this occurs, members shall stop, think, act safely, and contact Control or the Event Controller if unsure. Safety takes priority over routine traffic.'),1,'L',true);
        $p->Ln(3);

        // Section G: Risk Register
        $p->AddPage();
        $this->sectionHead('G. Risk Register');
        $p->SetFillColor(51,65,85);
        $p->SetTextColor(255,255,255);
        $p->SetFont('Arial','B',8);
        $p->Cell(38,7,$this->u('Hazard'),1,0,'C',true);
        $p->Cell(28,7,$this->u('Who at Risk'),1,0,'C',true);
        $p->Cell(68,7,$this->u('Control Measures'),1,0,'C',true);
        $p->Cell(20,7,$this->u('Likelihood'),1,0,'C',true);
        $p->Cell(20,7,$this->u('Severity'),1,0,'C',true);
        $p->Cell(0,7,$this->u('Residual'),1,1,'C',true);
        $p->SetTextColor(0,0,0);
        $p->SetFont('Arial','',8);

        $residualColours = ['Low'=>[209,250,229],'Medium'=>[254,243,199],'High'=>[254,226,226]];
        $colW = [38, 28, 68, 20, 20, 22];
        $pageH = 196;
        $lh = 4.5;
        $p->SetAutoPageBreak(false);

        $wrapText = function(string $txt, float $w) use ($p): array {
            $p->SetFont('Arial','',8);
            $sp = $p->GetStringWidth(' ');
            $words = explode(' ', $txt);
            $wrapped = []; $cur = ''; $curW = 0;
            foreach ($words as $word) {
                $ww = $p->GetStringWidth($word);
                if ($cur !== '' && $curW + $sp + $ww > $w - 1.5) {
                    $wrapped[] = $cur; $cur = $word; $curW = $ww;
                } else {
                    $cur = $cur !== '' ? $cur.' '.$word : $word;
                    $curW = $cur !== $word ? $curW + $sp + $ww : $ww;
                }
            }
            if ($cur !== '') $wrapped[] = $cur;
            return $wrapped ?: [''];
        };

        foreach ($this->pack->risks as $i => $risk) {
            $rc = $residualColours[$risk->residual] ?? [255,255,255];
            $bg = $i%2===0 ? [248,250,252] : [255,255,255];

            $col0 = $wrapText($this->u($risk->hazard), $colW[0]);
            $col1 = $wrapText($this->u($risk->persons_at_risk ?? 'RAYNET Volunteers'), $colW[1]);
            $col2 = $wrapText($this->u($risk->controls), $colW[2]);
            $rows = max(count($col0), count($col1), count($col2));
            $h = $rows * $lh + 1;

            if ($p->GetY() + $h > $pageH) {
                $p->AddPage();
                $this->sectionHead('G. Risk Register (continued)');
                $p->SetFillColor(51,65,85);
                $p->SetTextColor(255,255,255);
                $p->SetFont('Arial','B',8);
                foreach (['Hazard','Who at Risk','Control Measures','Likelihood','Severity','Residual'] as $hi => $hd) {
                    $p->Cell($colW[$hi], 7, $this->u($hd), 1, 0, 'C', true);
                }
                $p->Ln();
                $p->SetTextColor(0,0,0);
                $p->SetFont('Arial','',8);
            }

            $x = $p->GetX(); $y = $p->GetY();

            $p->SetFillColor($bg[0],$bg[1],$bg[2]);
            $p->Rect($x, $y, $colW[0]+$colW[1]+$colW[2]+$colW[3]+$colW[4]+$colW[5], $h, 'F');

            $p->SetFont('Arial','',8);
            for ($r = 0; $r < $rows; $r++) {
                $border0 = $r===0 ? 'LT' : ($r===$rows-1 ? 'LB' : 'L');
                $p->SetXY($x, $y + $r*$lh);
                $p->Cell($colW[0], $lh, $col0[$r] ?? '', $border0, 0, 'L', false);
                $p->SetXY($x+$colW[0], $y + $r*$lh);
                $p->Cell($colW[1], $lh, $col1[$r] ?? '', $border0, 0, 'L', false);
                $p->SetXY($x+$colW[0]+$colW[1], $y + $r*$lh);
                $p->Cell($colW[2], $lh, $col2[$r] ?? '', $border0, 0, 'L', false);
            }

            $p->SetXY($x, $y);
            $p->Cell($colW[0], $h, '', 1, 0, 'L', false);
            $p->Cell($colW[1], $h, '', 1, 0, 'L', false);
            $p->Cell($colW[2], $h, '', 1, 0, 'L', false);

            $p->SetXY($x+$colW[0]+$colW[1]+$colW[2], $y);
            $p->Cell($colW[3], $h, $this->u($risk->likelihood), 1, 0, 'C', true);
            $p->Cell($colW[4], $h, $this->u($risk->severity),   1, 0, 'C', true);
            $p->SetFillColor($rc[0],$rc[1],$rc[2]);
            $p->Cell($colW[5], $h, $this->u($risk->residual),   1, 0, 'C', true);
            $p->SetXY($x, $y + $h);
        }
        $p->SetAutoPageBreak(true, 15);

        // Section H: Auto-Generated Briefings and Checklists
        $risks = $this->pack->risks->map(fn($r) => $r->toArray())->toArray();
        $artefacts = BriefingEngine::generate($risks, $this->pack->toArray());
        $allBriefings  = $artefacts['briefings'];
        $allChecklists = $artefacts['checklists'];

        if (!empty($allBriefings) || !empty($allChecklists)) {
            $p->AddPage();
            $this->sectionHead('H. Generated Briefings and Checklists');
            $p->SetFont('Arial','I',8);
            $p->SetTextColor(107,127,150);
            $p->Cell(0,5,$this->u('Auto-generated from risk controls. Review before use.'),0,1,'L',false);
            $p->SetTextColor(0,0,0);
            $p->Ln(2);
        }

        foreach ($allBriefings as $brief) {
            if ($p->GetY() > 230) $p->AddPage();

            // Header bar
            $p->SetFillColor(0,51,102);
            $p->SetTextColor(255,255,255);
            $p->SetFont('Arial','B',10);
            $p->Cell(0,8,$this->u($brief['ref'].' — '.$brief['title']),0,1,'L',true);
            $p->SetTextColor(0,0,0);

            // Trigger and audience
            $p->SetFont('Arial','I',8);
            $p->SetFillColor(232,238,245);
            $p->Cell(0,5,$this->u('Generated because: '.$brief['trigger'].'  Audience: '.($brief['audience']??'All operators')),0,1,'L',true);
            $p->Ln(2);

            // Body
            $p->SetFont('Arial','',9);
            $p->SetFillColor(255,255,255);
            if (!empty($brief['body'])) {
                $p->MultiCell(0,5,$this->u($brief['body']),0,'L',true);
                $p->Ln(2);
            }

            // Checklist items (minimum expectations)
            if (!empty($brief['checklist_items'])) {
                $p->SetFont('Arial','B',8);
                $p->Cell(0,5,$this->u('Minimum expectations:'),0,1,'L',false);
                $p->SetFont('Arial','',9);
                foreach ($brief['checklist_items'] as $item) {
                    $p->Cell(0,5,$this->u(html_entity_decode('&#9744;',ENT_HTML5,'UTF-8').' '.$item),0,1,'L',false);
                }
                $p->Ln(2);
            }

            // If section
            if (!empty($brief['if_section'])) {
                $p->SetFont('Arial','B',8);
                $p->Cell(0,5,$this->u($brief['if_section']['label']),0,1,'L',false);
                $p->SetFont('Arial','',9);
                foreach ($brief['if_section']['items'] as $item) {
                    $p->Cell(0,5,$this->u('    '.$item),0,1,'L',false);
                }
                $p->Ln(2);
            }

            // Do not
            if (!empty($brief['do_not'])) {
                $p->SetFont('Arial','B',8);
                $p->Cell(0,5,$this->u('Do not:'),0,1,'L',false);
                $p->SetFont('Arial','',9);
                foreach ($brief['do_not'] as $item) {
                    $p->Cell(0,5,$this->u('    '.$item),0,1,'L',false);
                }
                $p->Ln(2);
            }

            // Instructions
            if (!empty($brief['instructions'])) {
                $p->SetFont('Arial','',9);
                foreach ($brief['instructions'] as $instr) {
                    if (trim($instr)) $p->MultiCell(0,5,$this->u($instr),0,'L',false);
                }
                $p->Ln(2);
            }

            // Escalation
            if (!empty($brief['escalation'])) {
                $p->SetFillColor(254,226,226);
                $p->SetTextColor(153,27,27);
                $p->SetFont('Arial','B',8);
                $p->MultiCell(0,5,$this->u('Escalation: '.$brief['escalation']),1,'L',true);
                $p->SetTextColor(0,0,0);
                $p->Ln(2);
            }

            // Confirm
            if (!empty($brief['confirm'])) {
                $p->SetFont('Arial','B',8);
                $p->Cell(0,5,$this->u('Confirm:'),0,1,'L',false);
                $p->SetFont('Arial','',9);
                foreach ($brief['confirm'] as $conf) {
                    $p->Cell(0,5,$this->u(html_entity_decode('&#9744;',ENT_HTML5,'UTF-8').' '.$conf),0,1,'L',false);
                }
            }

            $p->Ln(5);
            // Separator line
            $p->SetDrawColor(200,200,200);
            $p->Line($p->GetX(), $p->GetY(), $p->GetX()+180, $p->GetY());
            $p->SetDrawColor(0,0,0);
            $p->Ln(4);
        }

        foreach ($allChecklists as $cl) {
            if ($p->GetY() > 220) $p->AddPage();

            // Header
            $p->SetFillColor(51,65,85);
            $p->SetTextColor(255,255,255);
            $p->SetFont('Arial','B',10);
            $p->Cell(0,8,$this->u($cl['ref'].' — '.$cl['title']),0,1,'L',true);
            $p->SetTextColor(0,0,0);

            // Trigger
            $p->SetFont('Arial','I',8);
            $p->SetFillColor(232,238,245);
            $p->Cell(0,5,$this->u('Generated because: '.$cl['trigger']),0,1,'L',true);
            if (!empty($cl['audience'])) {
                $p->Cell(0,5,$this->u('Audience: '.$cl['audience']),0,1,'L',true);
            }
            $p->Ln(2);

            // Frequency
            if (!empty($cl['frequency'])) {
                $p->SetFillColor(254,243,199);
                $p->SetTextColor(146,64,14);
                $p->SetFont('Arial','B',8);
                $p->Cell(0,5,$this->u('Frequency: '.$cl['frequency']),1,1,'L',true);
                $p->SetTextColor(0,0,0);
                $p->Ln(2);
            }

            // Sections
            $sections = $cl['sections'] ?? ($cl['checks'] ? ['Checks' => $cl['checks']] : []);
            foreach ($sections as $secTitle => $items) {
                $p->SetFillColor(232,238,245);
                $p->SetFont('Arial','B',9);
                $p->Cell(0,5,$this->u($secTitle.':'),0,1,'L',true);
                $p->SetFont('Arial','',9);
                $p->SetFillColor(255,255,255);
                foreach ((array)$items as $item) {
                    if (trim($item)) $p->Cell(0,5,$this->u(html_entity_decode('&#9744;',ENT_HTML5,'UTF-8').' '.$item),0,1,'L',false);
                }
                $p->Ln(2);
            }

            // Stop if
            if (!empty($cl['stop_if'])) {
                $p->SetFillColor(254,226,226);
                $p->SetTextColor(153,27,27);
                $p->SetFont('Arial','B',8);
                $p->Cell(0,5,$this->u('Stop immediately if:'),0,1,'L',true);
                $p->SetFont('Arial','',9);
                foreach ($cl['stop_if'] as $s) {
                    $p->Cell(0,5,$this->u('    '.$s),0,1,'L',false);
                }
                $p->SetTextColor(0,0,0);
                $p->Ln(2);
            }

            // Escalate if
            if (!empty($cl['escalate_if'])) {
                $p->SetFillColor(254,226,226);
                $p->SetTextColor(153,27,27);
                $p->SetFont('Arial','B',8);
                $p->Cell(0,5,$this->u('Escalate if:'),0,1,'L',true);
                $p->SetFont('Arial','',9);
                foreach ($cl['escalate_if'] as $esc) {
                    $p->Cell(0,5,$this->u('    '.$esc),0,1,'L',false);
                }
                $p->SetTextColor(0,0,0);
                $p->Ln(2);
            }

            // Actions
            if (!empty($cl['actions'])) {
                $p->SetFont('Arial','B',8);
                $p->Cell(0,5,$this->u('Actions:'),0,1,'L',false);
                $p->SetFont('Arial','',9);
                foreach ($cl['actions'] as $a) {
                    $p->Cell(0,5,$this->u('    '.$a),0,1,'L',false);
                }
                $p->Ln(2);
            }

            // Sign off
            if (!empty($cl['sign_off'])) {
                $p->SetFont('Arial','',9);
                $p->Ln(2);
                foreach ($cl['sign_off'] as $sf) {
                    $p->Cell(70,7,$this->u($sf.': _________________________'),1,0,'L',false);
                }
                $p->Ln();
            }

            $p->Ln(5);
            $p->SetDrawColor(200,200,200);
            $p->Line($p->GetX(), $p->GetY(), $p->GetX()+180, $p->GetY());
            $p->SetDrawColor(0,0,0);
            $p->Ln(4);
        }

        // Section J: Further controls required
        $highRisks = $this->pack->risks->where('residual','High');
        if ($highRisks->count() > 0) {
            $p->Ln(3);
            \$this->sectionHead('J. Further Controls Required');
            $p->SetFillColor(254,226,226);
            $p->SetFont('Arial','',9);
            $p->SetTextColor(153,27,27);
            foreach ($highRisks as $r) {
                $p->MultiCell(0,5,$this->u('• '.$r->hazard.': '.$r->cause.' — Further controls or Group Controller approval required before proceeding.'),1,'L',true);
            }
            $p->SetTextColor(0,0,0);
            $p->Ln(3);
        }

        // Section I: Approval block
        \$this->sectionHead('K. Approval Block');
        $p->SetFillColor(255,255,255);
        $p->SetFont('Arial','',9);
        $statusStr = ucfirst($this->pack->status).($this->pack->approved_at?' | Approved: '.$this->pack->approved_at->format('d M Y H:i'):'');
        $p->Cell(0,6,$this->u('Assessment Status: '.$statusStr),1,1,'L',true);
        if ($this->pack->approval_statement) {
            $p->MultiCell(0,5,$this->u($this->pack->approval_statement),1,'L',true);
        }
        $p->Cell(0,8,$this->u('I confirm that this event plan has been reviewed and is suitable for Liverpool RAYNET use, subject to the controls listed.'),1,1,'L',true);
        $p->Ln(2);
        $p->Cell(90,9,$this->u('Signed: ________________________________'),1,0,'L',true);
        $p->Cell(70,9,$this->u('Name: ________________________'),1,0,'L',true);
        $p->Cell(0,9,$this->u('Date: ______________'),1,1,'L',true);

        $this->footer();
        return $p->Output('S');
    }

    public function operatorBrief(): string {
        require_once app_path('Libraries/fpdf/fpdf.php');
        $this->pdf = new \FPDF('P','mm','A4');
        $p = $this->pdf;
        $p->SetMargins(15,15,15);
        $p->SetAutoPageBreak(true,15);
        $p->AddPage();

        $this->header('Operator Information Sheet', 'v'.$this->pack->version);
        $this->ragBar();

        // Event
        $this->sectionHead('Event');
        $rows = [
            ['Event',             $this->pack->event_name],
            ['Date',              $this->pack->event_date?->format('d M Y')??'-'],
            ['User Service',      $this->pack->organiser_name??'-'],
            ['Operating Times',   ($this->pack->start_time??'TBC').' — '.($this->pack->finish_time??'TBC')],
            ['Location',          $this->pack->location??'-'],
        ];
        foreach ($rows as $i => $r) $this->row($r[0],$r[1],$i);
        $p->Ln(3);

        // Frequencies
        $this->sectionHead('Frequencies');
        $rows2 = [
            ['Primary Frequency',   $this->pack->primary_frequency??'TBC'],
            ['Alternative Frequency',$this->pack->secondary_frequency??'TBC'],
            ['Talk-Through / Repeater',$this->pack->talkthrough_used.' | '.($this->pack->repeater_details??'N/A')],
        ];
        foreach ($rows2 as $i => $r) $this->row($r[0],$r[1],$i);
        $p->Ln(3);

        // Controllers
        $this->sectionHead('Controllers');
        $rows3 = [
            ['Event Controller',    $this->pack->event_controller??'TBC'],
            ['Deputy Controller',   $this->pack->deputy_controller??'TBC'],
            ['Control Callsign',    $this->pack->control_callsign??'TBC'],
            ['Net Control Location',$this->pack->net_control_location??'TBC'],
            ['Call-Round Interval', $this->pack->call_round_interval??'TBC'],
        ];
        foreach ($rows3 as $i => $r) $this->row($r[0],$r[1],$i);
        $p->Ln(3);

        // Net control instructions
        $this->sectionHead('Net Control Instructions');
        $p->SetFillColor(255,255,255);
        $p->MultiCell(0,5,$this->u('All calls should be directed to Control unless otherwise instructed. Emergency or urgent traffic shall take priority over routine reporting. Maintain listening watch on the primary frequency at all times.'),1,'L',true);
        $p->Ln(3);

        // Emergency and urgent calls
        $this->sectionHead('Emergency and Urgent Calls');
        $p->SetFillColor(254,226,226);
        $p->SetTextColor(153,27,27);
        $p->SetFont('Arial','B',9);
        $p->MultiCell(0,6,$this->u('EMERGENCY: "Emergency message, emergency message from [tactical callsign], over."'),1,'L',true);
        $p->SetFillColor(254,243,199);
        $p->SetTextColor(146,64,14);
        $p->MultiCell(0,6,$this->u('URGENT: "Urgent message, urgent message from [tactical callsign], over."'),1,'L',true);
        $p->SetTextColor(0,0,0);
        $p->SetFont('Arial','',9);
        $p->Ln(3);

        // Operator schedule / checkpoint list
        if ($this->pack->posts->count() > 0) {
            $this->sectionHead('Operator Schedule and Checkpoint List');
            $p->SetFillColor(51,65,85);
            $p->SetTextColor(255,255,255);
            $p->SetFont('Arial','B',8);
            $p->Cell(30,6,$this->u('Tactical CS'),1,0,'C',true);
            $p->Cell(38,6,$this->u('Post Name'),1,0,'C',true);
            $p->Cell(45,6,$this->u('Location / Grid Ref'),1,0,'C',true);
            $p->Cell(18,6,$this->u('Start'),1,0,'C',true);
            $p->Cell(18,6,$this->u('Finish'),1,0,'C',true);
            $p->Cell(0,6,$this->u('Min Ops'),1,1,'C',true);
            $p->SetTextColor(0,0,0);
            $p->SetFont('Arial','',8);
            foreach ($this->pack->posts as $i => $post) {
                $locStr = $post->location??'';
                if ($post->grid_ref) $locStr .= ' ['.$post->grid_ref.']';
                if ($post->what3words) $locStr .= ' ///'.$post->what3words;
                $p->SetFillColor($i%2===0?245:255,$i%2===0?247:255,$i%2===0?250:255);
                $p->Cell(30,5,$this->u($post->tactical_callsign??'-'),1,0,'L',true);
                $p->Cell(38,5,$this->u($post->post_name),1,0,'L',true);
                $p->Cell(45,5,$this->u($locStr),1,0,'L',true);
                $p->Cell(18,5,$this->u($post->start_time??'-'),1,0,'C',true);
                $p->Cell(18,5,$this->u($post->finish_time??'-'),1,0,'C',true);
                $p->Cell(0,5,$this->u((string)$post->minimum_operators),1,1,'C',true);
                if ($post->access_notes) {
                    $p->SetFillColor(240,244,248);
                    $p->MultiCell(0,4,$this->u('    Access: '.$post->access_notes),0,'L',true);
                }
            }
            $p->Ln(3);
        }

        // Organiser notes
        if ($this->pack->event_description) {
            $this->sectionHead('Organiser Notes');
            $p->SetFillColor(255,255,255);
            $p->MultiCell(0,5,$this->u($this->pack->event_description),1,'L',true);
            $p->Ln(3);
        }

        // Arrival and parking
        $this->sectionHead('Arrival and Parking Instructions');
        $p->SetFillColor(255,255,255);
        $accessStr = implode(', ', (array)($this->pack->access_conditions??['See event briefing']));
        $p->MultiCell(0,5,$this->u('Access conditions: '.$accessStr.($this->pack->welfare_other?"\n".$this->pack->welfare_other:'')),1,'L',true);
        $p->Ln(3);

        // Welfare
        $this->sectionHead('Welfare Arrangements');
        $welfare = implode(', ', (array)($this->pack->facilities??['See event briefing']));
        $p->SetFillColor(255,255,255);
        $p->MultiCell(0,5,$this->u('Facilities: '.$welfare.($this->pack->welfare_food?"\nFood/refreshments: ".$this->pack->welfare_food:'').($this->pack->welfare_other?"\n".$this->pack->welfare_other:'')),1,'L',true);

        // Briefing notes from risks
        $briefingRisks = $this->pack->risks->filter(fn($r)=>!empty($r->briefing_note));
        if ($briefingRisks->count() > 0) {
            $p->Ln(3);
            $this->sectionHead('Operational Safety Briefing Notes');
            $p->SetFillColor(255,255,255);
            foreach ($briefingRisks as $r) {
                $p->MultiCell(0,5,$this->u('• '.$r->briefing_note),1,'L',true);
            }
        }

        $this->footer();
        return $p->Output('S');
    }

    public function assistanceRequest(): string {
        require_once app_path('Libraries/fpdf/fpdf.php');
        $this->pdf = new \FPDF('P','mm','A4');
        $p = $this->pdf;
        $p->SetMargins(15,15,15);
        $p->SetAutoPageBreak(true,15);
        $p->AddPage();

        $this->header('External Assistance Request');

        $this->sectionHead('Event Details');
        $rows = [
            ['Event Name',          $this->pack->event_name],
            ['Group',               '10/ME/179 Liverpool RAYNET'],
            ['Date',                $this->pack->event_date?->format('d M Y')??'-'],
            ['Location',            $this->pack->location??'-'],
            ['Town / Area',         $this->pack->town_area??'-'],
            ['Start / Finish Times', ($this->pack->start_time??'-').' — '.($this->pack->finish_time??'-')],
            ['Contact Name',        $this->pack->assistance_contact??'-'],
            ['Contact Phone/Email', $this->pack->assistance_phone_email??'-'],
            ['Type of Duty',        $this->pack->duty_type??'-'],
            ['Number of Outstations',$this->pack->outstations??'-'],
            ['Expected Message Traffic',$this->pack->traffic_level??'-'],
            ['Skill Level Required',$this->pack->skill_level??'-'],
            ['Equipment / Power',   $this->pack->equipment_power??'-'],
            ['Operating Environment',$this->pack->operating_environment??'-'],
            ['Food / Refreshments', $this->pack->welfare_food??'-'],
            ['Other Welfare',       $this->pack->welfare_other??'-'],
            ['Visibility Status',   $this->pack->assistance_visible?'Public':'Internal only'],
        ];
        foreach ($rows as $i => $r) $this->row($r[0],$r[1],$i);

        if ($this->pack->duty_description) {
            $p->Ln(3);
            $this->sectionHead('What Operators Will Be Doing');
            $p->SetFillColor(255,255,255);
            $p->MultiCell(0,5,$this->u($this->pack->duty_description),1,'L',true);
        }
        if ($this->pack->message_type) {
            $p->Ln(3);
            $this->sectionHead('Type of Information Being Passed');
            $p->SetFillColor(255,255,255);
            $p->MultiCell(0,5,$this->u($this->pack->message_type),1,'L',true);
        }

        $this->footer();
        return $p->Output('S');
    }

    public function joiningInstructions(): string {
        require_once app_path('Libraries/fpdf/fpdf.php');
        $this->pdf = new \FPDF('P','mm','A4');
        $p = $this->pdf;
        $p->SetMargins(15,15,15);
        $p->SetAutoPageBreak(true,15);
        $p->AddPage();

        $this->header('Joining Instructions');

        $this->sectionHead('Event Details');
        $rows = [
            ['Event',                $this->pack->event_name],
            ['Date',                 $this->pack->event_date?->format('d M Y')??'-'],
            ['Location',             $this->pack->location??'-'],
            ['Town / Area',          $this->pack->town_area??'-'],
            ['Event Controller',     $this->pack->event_controller??'TBC'],
            ['Control Callsign',     $this->pack->control_callsign??'TBC'],
            ['Primary Frequency',    $this->pack->primary_frequency??'TBC'],
            ['Briefing Time',        $this->pack->start_time??'TBC'],
        ];
        foreach ($rows as $i => $r) $this->row($r[0],$r[1],$i);
        $p->Ln(3);

        // Access conditions
        $accessConds = (array)($this->pack->access_conditions ?? []);
        $this->sectionHead('Arrival Route and Access');
        $p->SetFillColor(255,255,255);
        $p->MultiCell(0,5,$this->u(implode(', ', $accessConds) ?: 'Normal road access. See event details for specific directions.'),1,'L',true);
        $p->Ln(3);

        // Parking
        $hasPark  = in_array('Controlled parking', $accessConds);
        $hasPass  = in_array('Vehicle pass required', $accessConds);
        $hasOneWay= in_array('One-way system', $accessConds);
        $this->sectionHead('Parking');
        $parkStr = $hasPark ? 'Controlled parking in operation — follow signage.' : 'Standard parking available.';
        if ($hasPass) $parkStr .= ' Vehicle pass required — check with Event Controller.';
        $p->SetFillColor(255,255,255);
        $p->Cell(0,6,$this->u($parkStr),1,1,'L',true);
        $p->Ln(3);

        // Vehicle pass / one-way
        if ($hasPass || $hasOneWay) {
            $this->sectionHead('Access Restrictions');
            $restr = [];
            if ($hasPass) $restr[] = 'Vehicle pass required — confirm with Event Controller before arrival.';
            if ($hasOneWay) $restr[] = 'One-way traffic system in operation — follow directional signage.';
            if (in_array('Restricted access', $accessConds)) $restr[] = 'Restricted access applies — do not enter restricted zones.';
            $p->SetFillColor(255,255,255);
            foreach ($restr as $r) $p->MultiCell(0,5,$this->u('• '.$r),1,'L',true);
            $p->Ln(3);
        }

        // Contact on arrival
        $this->sectionHead('Contact on Arrival');
        $p->SetFillColor(255,255,255);
        $p->Cell(0,6,$this->u('Contact: '.($this->pack->event_controller??'Event Controller').' on '.($this->pack->primary_frequency??'primary frequency').' or in person at Net Control.'),1,1,'L',true);
        $p->Ln(3);

        // Sign-on process
        $this->sectionHead('Sign-On Process and Briefing');
        $p->SetFillColor(255,255,255);
        $p->MultiCell(0,5,$this->u('All operators to sign in with Group Controller or Event Controller on arrival. Briefing at '.$this->pack->start_time.' at Net Control location: '.($this->pack->net_control_location??'TBC').'. Ensure radio is programmed with primary and secondary frequencies before arrival.'),1,'L',true);
        $p->Ln(3);

        // Emergency contacts
        $this->sectionHead('Emergency Contacts');
        $p->SetFillColor(254,226,226);
        $p->SetFont('Arial','B',9);
        $p->SetTextColor(153,27,27);
        $p->Cell(0,6,$this->u('Emergency services: 999 | Event Controller: '.($this->pack->event_controller??'TBC').' | Primary frequency: '.($this->pack->primary_frequency??'TBC')),1,1,'L',true);
        $p->SetTextColor(0,0,0);
        $p->SetFont('Arial','',9);

        $this->footer();
        return $p->Output('S');
    }
}
