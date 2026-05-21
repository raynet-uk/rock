<?php
namespace App\Http\Controllers;

use App\Models\RiskAssessment;
use App\Services\RiskEngine;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;

class RiskAssessmentController extends Controller {

    public function index() {
        $assessments = RiskAssessment::where('user_id', auth()->id())
            ->orderByDesc('created_at')->paginate(20);
        return view('risk-assessment.index', compact('assessments'));
    }

    public function create() {
        return view('risk-assessment.wizard');
    }

    public function store(Request $request) {
        $data = $request->isJson() ? $request->json()->all() : $request->except(['_token']);
        $arrayFields = ['environment','event_type','other_agencies','roles','communications',
                        'infrastructure','terrain','facilities','equipment','fallback_comms','withdrawal_authority'];
        foreach ($arrayFields as $field) {
            if (isset($data[$field]) && !is_array($data[$field])) $data[$field] = [$data[$field]];
            if (!isset($data[$field])) $data[$field] = [];
        }
        $result = RiskEngine::generate($data);
        $ra = RiskAssessment::create(array_merge(
            array_diff_key($data, array_flip(['_token','_method'])),
            ['user_id' => auth()->id(), 'rag_status' => $result['rag'], 'status' => 'draft']
        ));
        AuditLogger::log('risk.created', auth()->user(), "Created risk assessment: {$ra->event_name}", ['ra_id' => $ra->id]);
        return response()->json([
            'success'   => true, 'id' => $ra->id, 'rag' => $result['rag'],
            'risks'     => $result['risks'], 'ragLabel' => $ra->ragLabel(), 'ragColour' => $ra->ragColour(),
        ]);
    }

    public function show(RiskAssessment $riskAssessment) {
        abort_if($riskAssessment->user_id !== auth()->id() && !auth()->user()->isAdmin(), 403);
        $result = RiskEngine::generate($riskAssessment->toArray());
        return view('risk-assessment.show', compact('riskAssessment', 'result'));
    }

    public function approve(Request $request, RiskAssessment $riskAssessment) {
        abort_if(!auth()->user()->isAdmin(), 403);
        if ($riskAssessment->rag_status === 'amber') {
            $request->validate(['confirm_amber' => ['required','accepted']]);
        }
        $riskAssessment->update(['status'=>'approved','approved_by'=>auth()->id(),'approved_at'=>now()]);
        AuditLogger::log('risk.approved', $riskAssessment->user, "Approved: {$riskAssessment->event_name}");
        return back()->with('success', 'Approved.');
    }

    public function generatePdf(RiskAssessment $riskAssessment) {
        abort_if($riskAssessment->user_id !== auth()->id() && !auth()->user()->isAdmin(), 403);
        $result   = RiskEngine::generate($riskAssessment->toArray());
        $pdf      = $this->buildPdf($riskAssessment, $result);
        $filename = ($riskAssessment->event_date ? $riskAssessment->event_date->format('Ymd') : date('Ymd'))
            . '_' . preg_replace('/[^A-Z0-9]/', '_', strtoupper($riskAssessment->event_name))
            . '_RISK_v' . $riskAssessment->version . '.pdf';
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    private function u(?string $s): string {
        if (!$s) return '';
        return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $s) ?: $s;
    }

    private function cell(object $pdf, float $w, float $h, ?string $txt, $border=0, int $ln=0, string $align='L', bool $fill=false): void {
        $pdf->Cell($w, $h, $this->u($txt), $border, $ln, $align, $fill);
    }

    private function buildPdf(RiskAssessment $ra, array $result): string {
        require_once app_path('Libraries/fpdf/fpdf.php');
        $pdf = new \FPDF('L','mm','A4');
        $pdf->SetMargins(12,12,12);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        // Header bar
        $pdf->SetFillColor(0,51,102);
        $pdf->SetTextColor(255,255,255);
        $pdf->SetFont('Arial','B',16);
        $this->cell($pdf, 0, 12, 'Liverpool RAYNET - Event Risk Assessment', 0, 1, 'C', true);
        $pdf->SetFont('Arial','',10);
        $this->cell($pdf, 0, 7,
            'Generated: '.date('d M Y H:i').' | Assessment v'.$ra->version.' | Status: '.strtoupper($ra->status),
            0, 1, 'C', true);
        $pdf->Ln(3);

        // RAG bar
        $ragColours = ['green'=>[5,150,105],'amber'=>[245,158,11],'red'=>[220,38,38]];
        $rc = $ragColours[$ra->rag_status] ?? [107,127,150];
        $pdf->SetFillColor($rc[0],$rc[1],$rc[2]);
        $pdf->SetTextColor(255,255,255);
        $pdf->SetFont('Arial','B',13);
        $this->cell($pdf, 0, 10, 'Overall RAG Status: '.$ra->ragLabel(), 0, 1, 'C', true);
        $pdf->Ln(4);

        // Section helper
        $sectionHead = function(string $title) use ($pdf) {
            $pdf->SetFillColor(0,51,102);
            $pdf->SetTextColor(255,255,255);
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(0, 8, $this->u($title), 0, 1, 'L', true);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFont('Arial','',10);
        };

        // Event details
        $sectionHead('Event Details');
        $details = [
            ['Event Name',          $ra->event_name],
            ['Location',            $ra->location ?? '-'],
            ['Date',                $ra->event_date ? $ra->event_date->format('d M Y') : '-'],
            ['Time',                ($ra->start_time ?? '-').' - '.($ra->finish_time ?? '-')],
            ['Expected Attendance', $ra->attendance ?? '-'],
            ['Environment',         implode(', ', $ra->environment ?? [])],
            ['Event Type',          implode(', ', $ra->event_type ?? [])],
            ['Other Agencies',      implode(', ', $ra->other_agencies ?? [])],
        ];
        foreach ($details as $i => $row) {
            $pdf->SetFillColor($i%2===0?245:255,$i%2===0?247:255,$i%2===0?250:255);
            $pdf->Cell(60,7,$this->u($row[0]),1,0,'L',true);
            $pdf->Cell(0,7,$this->u($row[1]),1,1,'L',true);
        }
        $pdf->Ln(4);

        // Deployment summary
        $sectionHead('Deployment Summary');
        $deploy = [
            ['Operators',       $ra->operator_count ?? '-'],
            ['Roles',           implode(', ', $ra->roles ?? [])],
            ['Communications',  implode(', ', $ra->communications ?? [])],
            ['Infrastructure',  implode(', ', $ra->infrastructure ?? [])],
            ['Duration',        $ra->deployment_duration ?? '-'],
            ['Lone Working',    $ra->lone_working ?? '-'],
            ['Night Operation', $ra->night_operation ?? '-'],
        ];
        foreach ($deploy as $i => $row) {
            $pdf->SetFillColor($i%2===0?245:255,$i%2===0?247:255,$i%2===0?250:255);
            $pdf->Cell(60,7,$this->u($row[0]),1,0,'L',true);
            $pdf->Cell(0,7,$this->u($row[1]),1,1,'L',true);
        }
        $pdf->Ln(4);

        // Dynamic risk statement
        $pdf->SetAutoPageBreak(false);
        $sectionHead('Dynamic Risk Statement');
        $pdf->SetFillColor(255,255,255);
        $pdf->MultiCell(0,6,$this->u('Health and safety is a shared responsibility. Situations may arise which could not reasonably have been foreseen. Where this occurs, members shall stop, think, act safely, and contact Control or the Event Controller if unsure. Safety takes priority over routine traffic.'),1,'L',true);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->Ln(4);

        // Risk register
        $pdf->AddPage();
        $sectionHead('Risk Register');

        // Table header
        $pdf->SetAutoPageBreak(false);
        $colW = [44, 27, 64, 20, 18, 22];
        $pageH = 196;

        $drawHeader = function() use ($pdf, $colW) {
            $pdf->SetFillColor(51,65,85);
            $pdf->SetTextColor(255,255,255);
            $pdf->SetFont('Arial','B',8);
            foreach (['Hazard','Who at Risk','Control Measures','Likelihood','Severity','Residual'] as $hi => $h) {
                $pdf->Cell($colW[$hi], 7, $h, 1, 0, 'C', true);
            }
            $pdf->Ln();
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFont('Arial','',8);
        };
        $drawHeader();

        // Word-wrap helper: returns array of lines fitting in $w mm
        $wrapText = function(string $txt, float $w) use ($pdf): array {
            $pdf->SetFont('Arial','',8);
            $sp = $pdf->GetStringWidth(' ');
            $words = explode(' ', $txt);
            $wrapped = []; $cur = ''; $curW = 0;
            foreach ($words as $word) {
                $ww = $pdf->GetStringWidth($word);
                if ($cur !== '' && $curW + $sp + $ww > $w - 1.5) {
                    $wrapped[] = $cur;
                    $cur = $word; $curW = $ww;
                } else {
                    if ($cur !== '') { $cur .= ' '.$word; $curW += $sp + $ww; }
                    else { $cur = $word; $curW = $ww; }
                }
            }
            if ($cur !== '') $wrapped[] = $cur;
            return $wrapped ?: [''];
        };

        $lh = 4.5;
        $residualColours = ['Low'=>[209,250,229],'Medium'=>[254,243,199],'High'=>[254,226,226]];

        foreach ($result['risks'] as $i => $risk) {
            $rc  = $residualColours[$risk['residual']] ?? [255,255,255];
            $bg  = $i%2===0 ? [248,250,252] : [255,255,255];

            $col0 = $wrapText($this->u($risk['hazard']), $colW[0]);
            $col1 = $wrapText($this->u($risk['persons_at_risk'] ?? 'RAYNET Volunteers'), $colW[1]);
            $col2 = $wrapText($this->u($risk['controls']), $colW[2]);
            $rows  = max(count($col0), count($col1), count($col2));
            $h     = $rows * $lh + 1;

            if ($pdf->GetY() + $h > $pageH) {
                $pdf->AddPage();
                $drawHeader();
            }

            $x = $pdf->GetX();
            $y = $pdf->GetY();

            // Draw background rectangles for full row
            $pdf->SetFillColor($bg[0],$bg[1],$bg[2]);
            $pdf->Rect($x, $y, $colW[0]+$colW[1]+$colW[2]+$colW[3]+$colW[4]+$colW[5], $h, 'F');

            // Draw text line by line for each cell
            $pdf->SetFont('Arial','',8);
            for ($r = 0; $r < $rows; $r++) {
                $pdf->SetXY($x,                              $y + $r*$lh);
                $pdf->Cell($colW[0], $lh, $col0[$r] ?? '', $r===0?'LT':($r===$rows-1?'LB':'L'), 0, 'L', false);
                $pdf->SetXY($x + $colW[0],                  $y + $r*$lh);
                $pdf->Cell($colW[1], $lh, $col1[$r] ?? '', $r===0?'LT':($r===$rows-1?'LB':'L'), 0, 'L', false);
                $pdf->SetXY($x + $colW[0]+$colW[1],         $y + $r*$lh);
                $pdf->Cell($colW[2], $lh, $col2[$r] ?? '', $r===0?'LT':($r===$rows-1?'LB':'L'), 0, 'L', false);
            }

            // Draw border around each text cell
            $pdf->SetXY($x, $y);
            $pdf->Cell($colW[0], $h, '', 1, 0, 'L', false);
            $pdf->Cell($colW[1], $h, '', 1, 0, 'L', false);
            $pdf->Cell($colW[2], $h, '', 1, 0, 'L', false);

            // Likelihood / Severity / Residual single-line cells
            $pdf->SetXY($x + $colW[0]+$colW[1]+$colW[2], $y);
            $pdf->Cell($colW[3], $h, $this->u($risk['likelihood']), 1, 0, 'C', true);
            $pdf->Cell($colW[4], $h, $this->u($risk['severity']),   1, 0, 'C', true);
            $pdf->SetFillColor($rc[0],$rc[1],$rc[2]);
            $pdf->Cell($colW[5], $h, $this->u($risk['residual']),   1, 0, 'C', true);
            $pdf->SetXY($x, $y + $h);
            $pdf->Ln(0);
        }
        $pdf->SetAutoPageBreak(true, 15);

        // Notes
        // Notes
        // Notes
        if ($ra->notes) {
            $pdf->Ln(4);
            $sectionHead('Additional Notes');
            $pdf->SetFillColor(255,255,255);
            $pdf->MultiCell(0,6,$this->u($ra->notes),1,'L',true);
        }

        // Approval block
        $pdf->Ln(4);
        $sectionHead('Approval Block');
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(0,7,$this->u('Status: '.ucfirst($ra->status).($ra->approved_at ? ' | Approved: '.$ra->approved_at->format('d M Y H:i') : '')),1,1,'L',true);
        $pdf->Cell(0,10,$this->u('Signed: ________________________________  Name: ________________________________  Date: ________________'),1,1,'L',true);

        // Footer
        $pdf->Ln(4);
        $pdf->SetFont('Arial','I',8);
        $pdf->SetTextColor(150,150,150);
        $pdf->Cell(0,5,$this->u('Liverpool RAYNET 10/ME/179 | Event Risk Assessment | Generated '.date('d M Y H:i').' | This document supports planning and does not replace dynamic risk assessment.'),0,1,'C');

        return $pdf->Output('S');
    }
}
