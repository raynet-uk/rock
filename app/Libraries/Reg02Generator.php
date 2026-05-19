<?php

if (!defined('FPDF_FONTPATH')) {
    define('FPDF_FONTPATH', __DIR__ . '/fpdf/font/');
}
require_once __DIR__ . '/fpdf/fpdf.php';

class Reg02Generator extends FPDF
{
    private array  $d   = [];   // form data
    private ?string $sig = null;
    private string $grp = '';

    public function build(array $data, ?string $signature, string $groupName): string
    {
        $this->d   = $data;
        $this->sig = $signature;
        $this->grp = $groupName;
        $this->SetMargins(14, 14, 14);
        $this->SetAutoPageBreak(false);
        $this->page1();
        $this->page2();
        $this->page3();
        $this->page4();
        return $this->Output('S');
    }

    // ── Helpers ──────────────────────────────────────────────────────────
    private function v(string $key): string
    {
        return $this->d[$key] ?? '';
    }
    private function tick(string $key): string
    {
        return !empty($this->d[$key]) ? 'x' : ' ';
    }
    private function W(): float { return 182; } // usable width

    // ── Page header ───────────────────────────────────────────────────────
    private function pageHeader(): void
    {
        $this->SetXY(14, 14);

        // Logo box (left 58mm)
        $this->SetDrawColor(0, 51, 102);
        $this->SetLineWidth(0.5);
        $this->Rect(14, 14, 58, 24);

        // "Communications by" label
        $this->SetFont('Helvetica', '', 6.5);
        $this->SetTextColor(0, 51, 102);
        $this->SetXY(15, 15.5);
        $this->Cell(56, 4, 'Communications by', 0, 1, 'C');

        // RAYNET in red
        $this->SetFont('Helvetica', 'B', 15);
        $this->SetTextColor(200, 16, 46);
        $this->SetX(15);
        $this->Cell(32, 7, 'RAYNET', 0, 0, 'R');

        // -UK in navy
        $this->SetFont('Helvetica', 'B', 11);
        $this->SetTextColor(0, 51, 102);
        $this->Cell(24, 7, '-UK', 0, 1, 'L');

        // www
        $this->SetFont('Helvetica', '', 6.5);
        $this->SetTextColor(0, 51, 102);
        $this->SetX(15);
        $this->Cell(56, 4, 'www.raynet-uk.net', 0, 1, 'C');

        // Company info (right side)
        $this->SetFont('Helvetica', '', 6.5);
        $this->SetTextColor(80, 80, 80);
        $this->SetXY(74, 14.5);
        $this->Cell($this->W() - 60, 4, 'A company limited by guarantee. Registered in England No 2771954', 0, 1, 'R');
        $this->SetX(74);
        $this->Cell($this->W() - 60, 4, 'Registered Charity in England & Wales (1047725) and in Scotland (SC046184)', 0, 1, 'R');
        $this->SetFont('Helvetica', 'B', 6.5);
        $this->SetX(74);
        $this->Cell($this->W() - 60, 4, 'Registered Office: 9 Conigre, Chinnor, Oxfordshire OX39 4JY', 0, 1, 'R');

        // Footer rule
        $this->SetDrawColor(200, 16, 46);
        $this->SetLineWidth(0.8);
        $this->Line(14, 38.5, 196, 38.5);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetY(40);
    }

    // ── Section heading ────────────────────────────────────────────────────
    private function sectionHead(string $num, string $title): void
    {
        $this->SetFont('Helvetica', 'B', 10.5);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(2);
        $this->Cell(8, 6, $num . '.', 0, 0, 'L');
        $this->Cell(0, 6, strtoupper($title), 0, 1, 'L');
        $this->Ln(1);
    }

    private function subHead(string $label): void
    {
        $this->SetFont('Helvetica', 'B', 9);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 5, $label, 0, 1, 'L');
        $this->Ln(1);
    }

    // ── Field drawing ──────────────────────────────────────────────────────
    // Full-width label + value row
    private function fullRow(string $label, string $value, float $lw = 52, float $h = 6): void
    {
        $this->SetFillColor(255, 255, 255);
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->SetTextColor(0, 0, 0);
        $this->Cell($lw, $h, $label . ':', 1, 0, 'L');
        $this->SetFont('Helvetica', '', 8.5);
        $this->Cell($this->W() - $lw, $h, $value, 1, 1, 'L');
    }

    // Two-column row: each half has its own label+value
    private function twoCol(
        string $l1, string $v1,
        string $l2, string $v2,
        float $lw = 32, float $h = 6
    ): void {
        $half = $this->W() / 2;
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->SetTextColor(0, 0, 0);
        $this->Cell($lw, $h, $l1 . ':', 1, 0, 'L');
        $this->SetFont('Helvetica', '', 8.5);
        $this->Cell($half - $lw, $h, $v1, 1, 0, 'L');
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell($lw, $h, $l2 . ':', 1, 0, 'L');
        $this->SetFont('Helvetica', '', 8.5);
        $this->Cell($half - $lw, $h, $v2, 1, 1, 'L');
    }

    // Empty labelled box with extra height
    private function sigBox(string $label, float $lw, float $vw, float $h): void
    {
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell($lw, $h, $label . ':', 1, 0, 'L');
        $this->SetFont('Helvetica', '', 8.5);
        $this->Cell($vw, $h, '', 1, 0, 'L');
    }

    // Draw a checkbox square and optional tick
    private function checkbox(float $x, float $y, bool $checked): void
    {
        $this->SetDrawColor(0, 0, 0);
        $this->Rect($x, $y, 4, 4);
        if ($checked) {
            $this->SetFont('Helvetica', 'B', 7);
            $this->SetTextColor(0, 0, 0);
            $this->SetXY($x + 0.3, $y + 0.2);
            $this->Cell(3.4, 3.6, 'X', 0, 0, 'C');
        }
    }

    // ── PAGE FOOTER ────────────────────────────────────────────────────────
    private function pageFooter(string $pageNum): void
    {
        $this->SetFont('Helvetica', 'I', 7);
        $this->SetTextColor(80, 80, 80);
        $this->SetXY(14, 284);
        $this->Cell($this->W() / 2, 4, 'Form REG-02 V2.2', 0, 0, 'L');
        $this->SetXY(14 + $this->W() / 2, 284);
        $this->Cell($this->W() / 2, 4, 'Page ' . $pageNum . ' of 4', 0, 0, 'R');
        $this->SetXY(14, 288);
        $this->Cell($this->W(), 4, '(Rev 2023-03)', 0, 0, 'L');
        $this->SetTextColor(0, 0, 0);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  PAGE 1
    // ══════════════════════════════════════════════════════════════════════
    private function page1(): void
    {
        $this->AddPage();
        $this->pageHeader();

        // Form title + Member ID box
        $this->SetY(42);
        $this->SetFont('Helvetica', 'B', 14);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(130, 8, 'REG-02 ' . chr(150) . ' NEW MEMBER APPLICATION', 0, 0, 'L');

        // Member ID box (top right)
        $mx = 152; $my = 41;
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.4);
        $this->Rect($mx, $my, 44, 10);
        $this->SetFont('Helvetica', 'B', 7.5);
        $this->SetXY($mx + 1, $my + 1);
        $this->Cell(42, 4, 'Member ID:', 0, 1, 'L');
        $this->SetFont('Helvetica', 'I', 6.5);
        $this->SetTextColor(100, 100, 100);
        $this->SetX($mx + 1);
        $this->Cell(42, 4, '(For Office Use Only)', 0, 0, 'L');
        $this->SetTextColor(0, 0, 0);

        // Instruction line
        $this->SetXY(14, 52);
        $this->SetFont('Helvetica', 'I', 8.5);
        $this->Cell($this->W(), 5, 'Please complete the form in  BLOCK CAPITALS  and  Black Ink', 0, 1, 'R');
        $this->Ln(2);

        // ── Section 1: Personal Details ─────────────────────────────────
        $this->sectionHead('1', 'PERSONAL DETAILS');

        $this->twoCol('Callsign', strtoupper($this->v('callsign')), '', '', 32, 6);
        // Clear the partial right cell (callsign only uses left half)
        // Actually do a full-width row for callsign
        $this->SetXY(14, $this->GetY() - 6);
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell(32, 6, 'Callsign:', 1, 0, 'L');
        $this->SetFont('Helvetica', '', 8.5);
        $this->Cell($this->W() - 32, 6, strtoupper($this->v('callsign')), 1, 1, 'L');

        $this->twoCol('Title', $this->v('title'), 'Surname', strtoupper($this->v('surname')));

        // Forenames + Known As
        $half = $this->W() / 2;
        $lw = 32;
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell($lw, 6, 'Forenames:', 1, 0, 'L');
        $this->SetFont('Helvetica', '', 8.5);
        $this->Cell($half - $lw, 6, strtoupper($this->v('forenames')), 1, 0, 'L');
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell($lw, 6, 'Known As:', 1, 0, 'L');
        $x = $this->GetX(); $y = $this->GetY();
        $this->SetFont('Helvetica', '', 8.5);
        $this->Cell($half - $lw, 6, $this->v('known_as'), 1, 1, 'L');
        // "Preferred name on ID Card" note
        $this->SetFont('Helvetica', 'I', 6.5);
        $this->SetTextColor(100, 100, 100);
        $this->SetXY($x, $y + 6);
        $this->Cell($half - $lw - 1, 3, 'Preferred name on ID Card', 0, 1, 'R');
        $this->SetTextColor(0, 0, 0);

        $dob = $this->v('dob') ? date('d/m/Y', strtotime($this->v('dob'))) : '';
        $this->twoCol('Date of Birth', $dob, '', '');
        // Redo DOB as full-width left
        $this->SetXY(14, $this->GetY() - 6);
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell(32, 6, 'Date of Birth:', 1, 0, 'L');
        $this->SetFont('Helvetica', '', 8.5);
        $this->Cell($this->W() - 32, 6, $dob, 1, 1, 'L');

        // Home Tel + Mobile
        $telEx  = !empty($this->d['home_tel_ex'])  ? ' (ex-dir)' : '';
        $mobEx  = !empty($this->d['mobile_ex']) ? ' (ex-dir)' : '';
        $lw2 = 28;
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell($lw2, 6, 'Home Tel No:', 1, 0, 'L');
        $this->SetFont('Helvetica', '', 8.5);
        $this->Cell($half - $lw2, 7.5, $this->v('home_tel') . $telEx, 'LRT', 0, 'L');
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell($lw2, 6, 'Mobile Phone No:', 1, 0, 'L');
        $this->SetFont('Helvetica', '', 8.5);
        $this->Cell($half - $lw2, 7.5, $this->v('mobile') . $mobEx, 'LRT', 1, 'L');
        // Ex-directory row
        $this->SetFont('Helvetica', 'I', 6.5);
        $this->SetTextColor(100, 100, 100);
        $this->Cell($lw2, 3.5, '', 'LB', 0);
        $this->Cell($half - $lw2, 3.5, 'Ex directory - Please tick', 'LRB', 0, 'C');
        $this->Cell($lw2, 3.5, '', 'LB', 0);
        $this->Cell($half - $lw2, 3.5, 'Ex directory - Please tick', 'LRB', 1, 'C');
        $this->SetTextColor(0, 0, 0);

        // Nationality + Home Address (2-row span)
        $addrText = str_replace(["\r\n", "\r", "\n"], ', ', $this->v('address'));
        $natY = $this->GetY();
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell($lw2, 6, 'Nationality:', 1, 0, 'L');
        $this->SetFont('Helvetica', '', 8.5);
        $this->Cell($half - $lw2, 6, $this->v('nationality'), 1, 0, 'L');
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell($lw2, 18, 'Home Address' . "\n" . 'inc. County &' . "\n" . 'Postcode:', 1, 0, 'L');
        $addrX = $this->GetX();
        $this->SetFont('Helvetica', '', 8.5);
        $this->MultiCell($half - $lw2, 6, $addrText, 1, 'L');
        $this->SetXY(14, $natY + 6);
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell($lw2, 6, 'Former/Dual' . "\n" . 'Nationality:', 1, 0, 'L');
        $this->SetFont('Helvetica', '', 8.5);
        $this->Cell($half - $lw2, 6, $this->v('former_nationality'), 1, 1, 'L');
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell($lw2, 6, 'Place of Birth:', 1, 0, 'L');
        $this->SetFont('Helvetica', '', 8.5);
        $this->Cell($half - $lw2, 6, $this->v('place_of_birth'), 1, 1, 'L');

        $emailY = max($this->GetY(), $natY + 18);
        $this->SetXY(14, $emailY);
        $this->fullRow('Email Address', $this->v('email'), 32, 6);

        // ── Section 2: Member Verification Check ─────────────────────────
        $this->sectionHead('2', 'MEMBER VERIFICATION CHECK');
        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(50, 50, 50);
        $this->MultiCell($this->W(), 4.5,
            'There is an increasing requirement on all types of organisations that its members\' are confirmed as to who they are. '
            . 'This equally applies to voluntary and charitable organisations of which RAYNET-UK is one and is considered to be good '
            . 'recruitment practice. The requirement includes checks on each member to confirm their identity, where they live, that '
            . 'they are entitled to reside in this country and that they have no unspent convictions. RAYNET-UK requires that all new '
            . 'members undergo this check upon membership application.',
        0, 'J');
        $this->SetTextColor(0, 0, 0);
        $this->Ln(4);

        // 2A
        $this->subHead('A:   CERTIFICATION OF IDENTITY  (Including Document Reference Number)');
        // Table header
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell(8, 6, '', 1, 0, 'C');
        $this->Cell(120, 6, 'Document Type:', 1, 0, 'C');
        $this->Cell(26, 6, 'Date of Issue:', 1, 0, 'C');
        $this->Cell($this->W() - 154, 6, '', 1, 1, 'C');
        // Date sub-label
        $this->SetFont('Helvetica', 'I', 6.5);
        $this->SetTextColor(100, 100, 100);
        // The "(dd/mm/yyyy)" sub-label under Date of Issue header
        $curY = $this->GetY();
        // Doc A row
        $this->SetTextColor(0, 0, 0);
        foreach ([['A', 'doc_a_type', 'doc_a_date', 'doc_a_ref'], ['B', 'doc_b_type', 'doc_b_date', 'doc_b_ref']] as $row) {
            $this->SetFont('Helvetica', 'B', 9);
            $this->Cell(8, 6, $row[0], 1, 0, 'C');
            $this->SetFont('Helvetica', '', 8);
            $rowY = $this->GetY();
            $this->SetFillColor(255, 255, 255);
            $this->Cell(120, 6, $this->v($row[1]), 1, 0, 'L');
            $this->Cell(26, 6, $this->v($row[2]), 1, 0, 'C');
            $this->Cell($this->W() - 154, 6, '', 1, 1, 'L');
            // Reference number row
            $this->Cell(8, 5, '', 1, 0, 'C');
            $this->SetFont('Helvetica', 'I', 7);
            $this->SetTextColor(100, 100, 100);
            $this->Cell($this->W() - 8, 5, 'Document Reference Number:  ' . $this->v($row[3]), 1, 1, 'L');
            $this->SetTextColor(0, 0, 0);
        }

        $this->pageFooter('1');
    }

    // ══════════════════════════════════════════════════════════════════════
    //  PAGE 2
    // ══════════════════════════════════════════════════════════════════════
    private function page2(): void
    {
        $this->AddPage();
        $this->pageHeader();
        $this->SetY(41);

        $this->subHead('B:   CRIMINAL RECORD DECLARATION');

        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(50, 50, 50);
        $this->MultiCell($this->W(), 4.3,
            'RAYNET-UK may require access to or hold material or information that is the property of the Government. '
            . 'The organisation has a duty to protect these assets while in its possession and this obligation extends to its members. '
            . 'Since you are or may become such a person, please complete the following sections:',
        0, 'J');
        $this->SetTextColor(0, 0, 0);
        $this->Ln(3);

        $questions = [
            ['1.', 'criminal_1', 'criminal_1_detail',
                'Have you ever been convicted or found guilty by a Court of any offence in any country (excluding parking but including all '
                . 'motoring offences even where a spot fine has been administered by the police), or have you ever received a community rehabilitation order '
                . '(previously called probation), or absolutely/conditionally discharged or bound over after being charged with any offence, or is there any '
                . 'action pending against you?  You need not declare convictions which are classed as "spent " under the Rehabilitation of Offenders Act '
                . '(1974). Please give dates and sentence.'],
            ['2.', 'criminal_2', 'criminal_2_detail',
                'Have you ever been convicted by a Court Martial or sentenced to detention or dismissal whilst serving in the Armed Forces of the UK or '
                . 'any Commonwealth or foreign country? You need not declare convictions which are classed as "spent " under the Rehabilitation of Offenders '
                . 'Act (1974). Please give dates and sentence.'],
            ['3.', 'criminal_3', 'criminal_3_detail',
                'Do you know of any other matter in your background that might cause your reliability or suitability to have access to government assets '
                . 'to be called into question? Please give details.'],
        ];

        foreach ($questions as $q) {
            $this->SetFont('Helvetica', '', 8);
            $this->SetTextColor(50, 50, 50);
            $this->Cell(6, 4.3, $q[0], 0, 0, 'R');
            $this->MultiCell($this->W() - 6, 4.3, $q[3], 0, 'J');
            $this->SetTextColor(0, 0, 0);
            $this->Ln(1);

            // "If Yes" line with Yes/No boxes
            $this->SetFont('Helvetica', 'I', 8);
            $this->SetTextColor(50, 50, 50);
            $this->Cell(52, 5, 'If Yes, please give details here', 0, 0, 'L');
            $this->SetTextColor(0, 0, 0);
            $this->SetFont('Helvetica', '', 8.5);

            $isYes = ($this->v($q[1]) === 'yes');
            $isNo  = ($this->v($q[1]) === 'no' || $this->v($q[1]) === '');

            // Yes box
            $bx = $this->GetX(); $by = $this->GetY();
            $this->SetDrawColor(0, 0, 0);
            $this->Cell(14, 5, '', 1, 0, 'C');
            if ($isYes) {
                $this->SetFont('Helvetica', 'B', 8);
                $this->SetXY($bx, $by);
                $this->Cell(10, 5, 'X', 0, 0, 'C');
                $this->SetFont('Helvetica', '', 8.5);
                $this->SetXY($bx + 14, $by);
            }
            $this->Cell(10, 5, 'Yes', 0, 0, 'L');
            $nx = $this->GetX(); $ny = $this->GetY();
            $this->Cell(14, 5, '', 1, 0, 'C');
            if ($isNo) {
                $this->SetFont('Helvetica', 'B', 8);
                $this->SetXY($nx, $ny);
                $this->Cell(10, 5, 'X', 0, 0, 'C');
                $this->SetFont('Helvetica', '', 8.5);
                $this->SetXY($nx + 14, $ny);
            }
            $this->Cell(10, 5, 'No', 0, 1, 'L');

            $this->Ln(1);
            // Detail box
            $detail = $this->v($q[2]);
            $this->SetFont('Helvetica', '', 8);
            $this->MultiCell($this->W(), 14, $detail, 1, 'L');
            $this->Ln(4);
        }

        // Section 3
        $this->sectionHead('3', 'RAYNET-UK EMAIL ADDRESS');
        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(50, 50, 50);
        $this->MultiCell($this->W(), 4.3,
            'All members will be allocated a RAYNET-UK M356 account. This gives the member an @raynet-uk.net email address as '
            . 'part of a Microsoft 365 package. Also included are online versions of Microsoft Office packages (Word, Excel, '
            . 'Powerpoint, Outlook). All national mailings will be sent to the members M365 email addresses, incoming emails can be '
            . 'forwarded to the members personal email addresses.',
        0, 'J');
        $this->Ln(4);
        $this->SetFont('Helvetica', '', 8);
        $this->Cell($this->W(), 4.3, 'Members must adhere to the Microsoft 365 End User Agreement as per the REG-02 Supplementary Information', 0, 1, 'J');
        $this->SetTextColor(0, 0, 0);

        $this->pageFooter('2');
    }

    // ══════════════════════════════════════════════════════════════════════
    //  PAGE 3
    // ══════════════════════════════════════════════════════════════════════
    private function page3(): void
    {
        $this->AddPage();
        $this->pageHeader();
        $this->SetY(41);

        // Section 4: Member Communications
        $this->sectionHead('4', 'MEMBER COMMUNICATIONS');

        $this->SetFont('Helvetica', 'I', 7.5);
        $this->SetTextColor(80, 80, 80);
        $this->Cell($this->W(), 4, 'Please tick where appropriate', 0, 1, 'R');
        $this->SetTextColor(0, 0, 0);
        $this->Ln(1);

        $half = $this->W() / 2;
        $comms = [
            ['Yes please - I would like to receive RAYNET National communications by email (these will be sent to the member\'s RAYNET M365 Account)',
             'comms_national_email',
             'Yes please - I would like to receive RAYNET Group communications by email',
             'comms_group_email'],
            ['Yes please - I would like to receive RAYNET National communications by telephone',
             'comms_national_tel',
             'Yes please - I would like to receive RAYNET Group communications by telephone',
             'comms_group_tel'],
            ['Yes, please - I would like to receive RAYNET National communications by SMS',
             'comms_national_sms',
             'Yes, please - I would like to receive RAYNET Group communications by SMS',
             'comms_group_sms'],
            ['Yes please - I would like to receive RAYNET National communications by post',
             'comms_national_post',
             'Yes please - I would like to receive RAYNET Group communications by post',
             'comms_group_post'],
        ];

        foreach ($comms as $row) {
            $rowY = $this->GetY();
            $this->SetFont('Helvetica', '', 7.5);
            // Left cell content
            $this->MultiCell($half - 12, 13, $row[0], 0, 'L');
            $leftH = $this->GetY() - $rowY;
            $h = max($leftH, 13);
            // Draw left border box
            $this->SetDrawColor(0, 0, 0);
            $this->Rect(14, $rowY, $half - 12, $h);
            // Checkbox left
            $cbX = 14 + $half - 12; $cbY = $rowY + ($h - 5) / 2;
            $this->Rect($cbX, $cbY, 5, 5);
            if (!empty($this->d[$row[1]])) {
                $this->SetFont('Helvetica', 'B', 8);
                $this->SetXY($cbX, $cbY);
                $this->Cell(5, 5, 'X', 0, 0, 'C');
            }
            // Right text
            $this->SetXY($cbX + 7, $rowY);
            $this->SetFont('Helvetica', '', 7.5);
            $this->MultiCell($half - 12, 13, $row[2], 0, 'L');
            $this->Rect($cbX + 7, $rowY, $half - 12, $h);
            // Checkbox right
            $cbX2 = $cbX + 7 + $half - 12;
            $this->Rect($cbX2, $cbY, 5, 5);
            if (!empty($this->d[$row[3]])) {
                $this->SetFont('Helvetica', 'B', 8);
                $this->SetXY($cbX2, $cbY);
                $this->Cell(5, 5, 'X', 0, 0, 'C');
            }
            $this->SetXY(14, $rowY + $h);
        }
        $this->Ln(4);

        // Section 5: Declaration
        $this->sectionHead('5', 'DECLARATION');
        $decls = [
            'I agree to become a member of RAYNET-UK',
            "I agree that I am in sympathy with the Company's aims and objects and agree to abide by its rules as circulated by RAYNET-UK",
            'Should the company be dissolved, I promise to pay the maximum of ' . chr(163) . '5.00 towards its debts if asked to do so',
            'I am over 18 years of age',
            'I declare that the information that I have given on this form is true and complete to the best of my knowledge and belief',
        ];
        $this->SetFont('Helvetica', '', 8.5);
        foreach ($decls as $d) {
            $this->MultiCell($this->W(), 5, $d, 0, 'L');
        }
        $this->Ln(3);

        // Signature row
        $sigY = $this->GetY();
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell(26, 15, 'Signature:', 1, 0, 'L');
        $sigBoxX = $this->GetX();
        $this->Cell(120, 15, '', 1, 0, 'L');
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell(14, 15, 'Date:', 1, 0, 'L');
        $this->SetFont('Helvetica', '', 8.5);
        $this->Cell($this->W() - 160, 15, date('d/m/Y'), 1, 1, 'L');

        // Embed signature image
        if (!empty($this->sig) && str_starts_with($this->sig, 'data:image/png;base64,')) {
            $b64  = substr($this->sig, strlen('data:image/png;base64,'));
            $data = base64_decode($b64);
            $tmp  = tempnam(sys_get_temp_dir(), 'reg02sig_') . '.png';
            file_put_contents($tmp, $data);
            try {
                $this->Image($tmp, $sigBoxX + 2, $sigY + 1, 100, 13);
            } catch (\Throwable $e) {}
            @unlink($tmp);
        }

        $this->SetFont('Helvetica', 'I', 6.5);
        $this->SetTextColor(80, 80, 80);
        $this->Cell($this->W(), 4,
            !empty($this->sig) ? 'Digitally signed via ' . $this->grp . ' member portal' : 'Handwritten signature only please',
        0, 1, 'C');
        $this->SetTextColor(0, 0, 0);
        $this->Ln(5);

        // Section 6: Junior Members
        $this->sectionHead('6', 'DECLARATION FOR JUNIOR MEMBERS');
        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(50, 50, 50);
        $this->MultiCell($this->W(), 4.3,
            'I, the undersigned, am legally responsible for the above named applicant and certify that to the best of my knowledge '
            . 'and belief, they are in sympathy with the Company\'s aims and objectives and agrees to abide by its rules as circulated by '
            . 'RAYNET-UK in so far as it is fair and reasonable for a person under the age of 18 years to do so',
        0, 'J');
        $this->SetTextColor(0, 0, 0);
        $this->Ln(3);
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell(26, 8, 'Name:', 1, 0, 'L');
        $this->SetFont('Helvetica', '', 8.5);
        $this->Cell(120, 8, '', 1, 0, 'L');
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell(14, 8, 'Date:', 1, 0, 'L');
        $this->Cell($this->W() - 160, 8, '', 1, 1, 'L');
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell(26, 8, 'Signature:', 1, 0, 'L');
        $this->SetFont('Helvetica', '', 8.5);
        $this->Cell(120, 8, '', 1, 0, 'L');
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->Cell(18, 8, 'Parent', 1, 0, 'C');
        $this->Cell($this->W() - 164, 8, 'Guardian', 1, 1, 'C');
        $this->SetFont('Helvetica', 'I', 6.5);
        $this->SetTextColor(80, 80, 80);
        $this->Cell($this->W(), 4, 'Handwritten signature only please', 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);

        $this->pageFooter('3');
    }

    // ══════════════════════════════════════════════════════════════════════
    //  PAGE 4
    // ══════════════════════════════════════════════════════════════════════
    private function page4(): void
    {
        $this->AddPage();
        $this->pageHeader();
        $this->SetY(41);

        // Section 7: Verifying Officer
        $this->sectionHead('7', 'VERIFYING GROUP CONTROLLER/REGISTRATIONS OFFICER');
        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(50, 50, 50);
        $this->MultiCell($this->W(), 4.3,
            'I have personally examined the documents submitted in Section 2 of this form and have satisfactorily established the '
            . 'identity of the applicant',
        0, 'J');
        $this->SetTextColor(0, 0, 0);
        $this->Ln(3);

        $verFields = [
            ['Full Name:',      16],
            ['Callsign:',       8],
            ['Group Name:',     10],
            ['Group Number:',   10],
            ['MVC/BPSS Number:', 10],
            ['Officer Role:',   10],
            ['Signature:',      14],
            ['Date:',           10],
        ];
        foreach ($verFields as $f) {
            $lw = 48;
            $this->SetFont('Helvetica', 'B', 8.5);
            $this->Cell($lw, $f[1], $f[0], 1, 0, 'L');
            $this->SetFont('Helvetica', '', 8.5);
            $this->Cell($this->W() - $lw, $f[1], '', 1, 1, 'L');
        }
        $this->Ln(4);

        // MVC sub-note
        $this->SetFont('Helvetica', 'I', 6.5);
        $this->SetTextColor(80, 80, 80);
        $this->SetX(62);
        $this->Cell($this->W() - 48, 3, 'Can be found under membership record', 0, 1, 'L');
        $this->SetTextColor(0, 0, 0);
        $this->Ln(4);

        // Section 8: For Office Use Only
        $this->sectionHead('8', 'FOR OFFICE USE ONLY');
        $officeFields = [
            [['Form Received', 'Form Processed:']],
            [['Group Name:', 'Group Number:']],
            [['Member ID:', 'MVC ID:']],
            [['ID Card Printed:', 'ID Card Posted:']],
        ];
        $hw = $this->W() / 2;
        foreach ($officeFields as $pair) {
            $row = $pair[0];
            $lw = 36;
            $this->SetFont('Helvetica', 'B', 8.5);
            $this->Cell($lw, 8, $row[0], 1, 0, 'L');
            $this->SetFont('Helvetica', '', 8.5);
            $this->Cell($hw - $lw, 8, '', 1, 0, 'L');
            $this->SetFont('Helvetica', 'B', 8.5);
            $this->Cell($lw, 8, $row[1], 1, 0, 'L');
            $this->SetFont('Helvetica', '', 8.5);
            $this->Cell($hw - $lw, 8, '', 1, 1, 'L');
        }
        $this->Ln(6);

        // GDPR notice
        $this->SetFont('Helvetica', 'B', 7.5);
        $this->Cell(14, 4, 'Important:', 0, 0, 'L');
        $this->SetFont('Helvetica', '', 7.5);
        $this->SetTextColor(50, 50, 50);
        $this->MultiCell($this->W() - 14, 4,
            '   The UK GDPR is implemented under The Data Protection, Privacy and Electronic Communication ( Amendments etc) ( EU Exit ) '
            . 'Regulations 2019 SI2019/419 . You will supply this information to the appropriate officer in the organisation for the purpose of RAYNET-UK '
            . 'membership registration and ongoing membership operations both nationally and at group level. The Organisation will protect the information '
            . 'provided and ensure that it is not passed to anyone who is not authorised to see it.',
        0, 'J');
        $this->SetTextColor(0, 0, 0);

        $this->pageFooter('4');
    }
}
