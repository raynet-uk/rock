<?php
namespace App\Services;

class RiskEngine {

    public static function generate(array $data): array {
        return self::generateFull($data);
    }

    /**
     * RAG matrix from brief section 7:
     * L/L=Green, L/M=Green, L/H=Amber
     * M/L=Green, M/M=Amber, M/H=Red
     * H/L=Amber, H/M=Red, H/H=Red
     */
    private static function ragFromMatrix(string $likelihood, string $severity): string {
        $l = strtolower($likelihood[0]); // l, m, h
        $s = strtolower($severity[0]);   // l, m, h
        $matrix = [
            'll'=>'Low','lm'=>'Low','lh'=>'Medium',
            'ml'=>'Low','mm'=>'Medium','mh'=>'High',
            'hl'=>'Medium','hm'=>'High','hh'=>'High',
        ];
        return $matrix[$l.$s] ?? 'Medium';
    }

    public static function generateFull(array $data): array {
        $risks = [];

        $get = fn($k) => $data[$k] ?? null;
        $has = fn($k, $v) => in_array($v, (array)($data[$k] ?? []));
        $arr = fn($k) => (array)($data[$k] ?? []);

        // ── Weather exposure ──
        if ($get('weather_exposure') === 'High') {
            $risks[] = self::risk('Weather Exposure','High weather exposure during deployment','RAYNET Volunteers',
                'Suitable clothing mandatory. Regular welfare checks minimum every 30 minutes. Shelter identified and briefed. Welfare lead appointed. Hot and cold weather protocols briefed.',
                'Medium','Medium');
        } elseif ($get('weather_exposure') === 'Moderate') {
            $risks[] = self::risk('Weather Exposure','Moderate weather exposure','RAYNET Volunteers',
                'Operators to carry suitable clothing. Brief on welfare self-care. Water available at all times.',
                'Low','Medium');
        }

        // ── Hot weather ──
        if ($has('welfare_risks','Hot weather')) {
            $risks[] = self::risk('Heat Exposure','Risk of heat exhaustion or sunstroke during deployment','RAYNET Volunteers',
                'Water available at all times. Shade/shelter identified. Operators to report symptoms immediately. Avoid direct sun during peak hours where possible. Sun protection worn.',
                'Medium','Medium');
        }

        // ── Cold/wet/windy ──
        if ($has('welfare_risks','Cold/wet/windy weather')) {
            $risks[] = self::risk('Cold Weather and Exposure','Risk of hypothermia or cold injury','RAYNET Volunteers',
                'Warm, windproof and waterproof clothing mandatory. Hot drinks available. Welfare check-ins increased. Operators to report feeling unwell immediately. Deployment at remote/exposed posts reviewed.',
                'Medium','Medium');
        }

        // ── Weather contingency ──
        if (in_array($get('weather_contingency'), ['Red']) || $has('welfare_risks','weather_red')) {
            $risks[] = self::risk('Severe Weather Event','Red weather contingency in force','RAYNET Volunteers',
                'Suspend operations immediately if red alert issued. Withdrawal triggers pre-briefed. Do not redeploy until conditions improve. Withdrawal authority clearly identified.',
                'Medium','High', true);
        } elseif ($get('weather_contingency') === 'Amber') {
            $risks[] = self::risk('Adverse Weather','Amber weather contingency in place','RAYNET Volunteers',
                'Monitor conditions. Pre-defined withdrawal triggers briefed. Standby shelter locations identified.',
                'Medium','Medium');
        }

        // ── Temporary mast ──
        if ($has('equipment','Temporary mast') || $has('equipment','Antenna guys/ground anchors') || $has('equipment','Temporary Mast') || $has('equipment','Mast')) {
            $risks[] = self::risk('Erection, Collapse or Dismantling of Antennas and Masts','Use of temporary antenna mast','RAYNET Volunteers and Public',
                'Dynamic risk assessment before erection and dismantling. Guys and pegs installed correctly and made visible. Exclusion zone maintained. Damaged or suspect equipment not used. Gloves worn during erection. Overhead hazards checked before erection. No erection in high winds.',
                'Low','Medium');
        }

        // ── Generator ──
        if ($has('equipment','Generator') || $has('infrastructure','Generator') || $get('power_source') === 'Generator') {
            $risks[] = self::risk('Generator Operation','Use of petrol or diesel generator','RAYNET Volunteers and nearby persons',
                'Generator sited away from personnel and adequately ventilated. Fire extinguisher available within 5 metres. No refuelling while running. Carbon monoxide risk — never use indoors or in enclosed spaces. Fuel stored safely in appropriate containers.',
                'Low','High');
        }

        // ── Feeder cables / cabling / trip hazards ──
        if ($has('equipment','Feeder cables') || $has('equipment','Cabling') || $has('equipment','Temporary power') || $has('equipment','Mains power')) {
            $risks[] = self::risk('Trip Hazard — Cables and Equipment','Cables and equipment creating trip hazards at event','RAYNET Volunteers and Public',
                'All cables routed away from pedestrian areas or covered with cable protectors. Equipment positioned to minimise obstruction. Regular checks during event. Public access to technical areas managed.',
                'Medium','Low');
            $risks[] = self::risk('Electrical Fault and Burns — Temporary Power','Use of temporary power cabling and connections','RAYNET Volunteers and Public',
                'PAT-tested equipment only. RCD protection used on all mains connections. No connections made in wet conditions. Cables regularly inspected during event. Only competent persons to make and break connections.',
                'Low','High');
        }

        // ── Manual handling ──
        if ($has('equipment','Temporary mast') || $has('equipment','Generator') || $has('equipment','Shelter/gazebo') || $has('equipment','Lighting')) {
            $risks[] = self::risk('Manual Handling','Movement of heavy or bulky equipment','RAYNET Volunteers',
                'Operators to assess loads before lifting. Team lifting used for heavy items. Appropriate footwear worn. No manual handling on uneven ground without assessment. Trolleys or handling aids used where available.',
                'Medium','Low');
        }

        // ── Public-facing control area ──
        if ($has('equipment','Public-facing control area') || $has('equipment','Laptop/tablet logging')) {
            $risks[] = self::risk('Public Access to Control Area','Members of public accessing RAYNET control or technical area','RAYNET Volunteers and Public',
                'Control area clearly defined and signed. Physical barrier or marshalling used to prevent unauthorised access. Equipment secured and not accessible to public. All operators briefed on access management.',
                'Medium','Medium');
        }

        // ── RF exposure ──
        if ($has('equipment','Temporary mast') || $has('equipment','Vehicle antenna') || $has('equipment','Repeater')) {
            $risks[] = self::risk('RF Exposure','Elevated RF levels near operating antenna systems','RAYNET Volunteers and Public',
                'Exclusion zone maintained around operating antennas. Public access to antenna area prevented. Transmit power set to minimum required for communications. No contact with energised antenna elements.',
                'Low','Medium');
        }

        // ── Road exposure ──
        if ($get('road_exposure') === 'Active crossings' || $has('access_conditions','Active traffic nearby') || $has('access_conditions','Public road crossing')) {
            $risks[] = self::risk('Road Traffic — Active Crossings','Operators required to cross or work adjacent to active traffic','RAYNET Volunteers',
                'Hi-vis PPE mandatory at all roadside locations. Safe crossing points identified and briefed before deployment. No lone road crossing. Traffic management confirmed with event organiser. Do not direct traffic unless specifically authorised by emergency services. Park safely.',
                'Medium','Medium', true);
        } elseif ($get('road_exposure') === 'Adjacent') {
            $risks[] = self::risk('Road Traffic — Roadside Working','Operators working adjacent to live roads','RAYNET Volunteers',
                'Hi-vis PPE worn at all times. Work from a place of safety. Stand clear of live carriageway. Vehicles parked legally and safely. Do not direct traffic unless authorised.',
                'Low','High');
        }

        // ── Off-road driving ──
        if ($has('access_conditions','Unsuitable tracks') || $has('access_conditions','4x4 preferred') || $has('terrain','Off-road track')) {
            $risks[] = self::risk('Off-Road Driving to and from Checkpoint','Vehicles driven on unsuitable or off-road terrain','RAYNET Volunteers',
                'Organiser-approved routes only. Suitable maps or GPS carried. No driving on unsuitable tracks without prior clearance. Contact Control before proceeding if route is unclear. Vehicles must be suitable for terrain.',
                'Medium','Medium');
        }

        // ── Lone working ──
        if (in_array($get('lone_working'), ['Possible','Expected'])) {
            $esc = $get('lone_working') === 'Expected';
            $risks[] = self::risk('Lone Working','Operators working without direct supervision','RAYNET Volunteers',
                'Welfare calls at defined intervals — minimum every 30 minutes. Escalation procedure if no contact. Consider double-manning for exposed or remote posts. Emergency contact confirmed with Group Controller before deployment.',
                'Medium','Medium', $esc);
        }

        // ── Remote posts ──
        if ($get('access') === 'Remote' || $has('terrain','Remote') || $has('welfare_risks','Remote post')) {
            $risks[] = self::risk('Remote Location or Post','Limited emergency services access to remote post','RAYNET Volunteers',
                'Grid references shared with Group Controller before deployment. Emergency services access route confirmed. First aid kit carried at each remote post. Mobile coverage assessed. Check-in schedule maintained.',
                'Low','High');
        }

        // ── Night operations ──
        if ($get('night_operation') === 'Yes' || $has('welfare_risks','Night operation')) {
            $risks[] = self::risk('Night Operations','Operating in low-light or darkness','RAYNET Volunteers',
                'Torches or head torches mandatory. Hi-vis worn. Buddy system in operation. Reduced movement speeds. Hazards re-assessed at last light. Routes safety-checked in daylight where possible.',
                'Medium','Medium');
        }

        // ── Under-18s ──
        if ($get('under_18') === 'Yes' || $has('welfare_risks','Children present')) {
            $risks[] = self::risk('Under-18 Participants','Persons under 18 involved in deployment','Under-18 participants and RAYNET Volunteers',
                'Safeguarding policy applied. Under-18s not to work alone at any time. Parental or guardian consent obtained. DBS checks confirmed for all supervising adults.',
                'Low','High', true);
        }

        // ── Long deployment ──
        if (in_array($get('deployment_duration'), ['8–12 hours','Over 12 hours','8–12h','>12h'])) {
            $risks[] = self::risk('Operator Fatigue','Extended deployment exceeding 8 hours','RAYNET Volunteers',
                'Operator rotation implemented where possible. Scheduled rest breaks. Food and water provision confirmed. No driving while fatigued. Welfare check-ins increased for extended deployments.',
                'Medium','Medium');
        }

        // ── Public order / aggressive behaviour ──
        if (in_array($get('public_order'), ['Possible','Expected']) || $has('welfare_risks','Aggressive behaviour')) {
            $esc = $get('public_order') === 'Expected';
            $risks[] = self::risk('Public Order or Aggressive Behaviour','Risk of crowd disorder or hostile behaviour toward operators','RAYNET Volunteers',
                'Operators briefed to withdraw from confrontation immediately. Do not engage. Report to Event Controller and police immediately. Pre-defined safe withdrawal points known to all operators.',
                'Medium','High', $esc);
        }

        // ── Public interaction ──
        if ($has('welfare_risks','Public interaction')) {
            $risks[] = self::risk('Public Interaction','Regular interaction with members of the public during deployment','RAYNET Volunteers and Public',
                'Operators briefed on public-facing conduct. ID or tabard worn. Operators to refer inappropriate requests to Event Controller. Personal details not shared with members of the public.',
                'Low','Low');
        }

        // ── Horses ──
        if ($has('welfare_risks','Horses') || $has('welfare_risks','Animals')) {
            $risks[] = self::risk('Loose or Uncontrolled Animals','Horses or animals present at event site','RAYNET Volunteers',
                'Operators shall not attempt to catch or stop a loose horse or animal. Avoid crush points. Remain calm and still if approached by a horse. Inform Control and event staff immediately. Do not use radios near horses without warning.',
                'Medium','Low');
        }

        // ── Vulnerable persons ──
        if ($has('welfare_risks','Vulnerable persons')) {
            $risks[] = self::risk('Vulnerable Persons Present','Vulnerable adults or persons with additional needs at event','RAYNET Volunteers',
                'Safeguarding policy applied. Any welfare concern reported to Event Controller and appropriate agency immediately. Operators not to manage vulnerable persons directly — refer to event welfare team.',
                'Medium','Medium', true);
        }

        // ── Vehicles on site ──
        if ($get('vehicles_operating') === 'Yes') {
            $risks[] = self::risk('Vehicles Operating on Event Site','RAYNET vehicles moving on busy event site','RAYNET Volunteers and Event Participants',
                '5mph site speed limit observed. Pedestrian priority at all times. Reversing observer required for all manoeuvres. Handbrake applied when stationary. Keys removed when vehicle unattended.',
                'Low','Medium');
        }

        // ── Water hazard ──
        if ($has('terrain','Water nearby') || $has('terrain','Water')) {
            $risks[] = self::risk('Water Hazard','Operating in proximity to open water','RAYNET Volunteers',
                'Operators briefed on all water hazards at site. No approach to water edge unless operationally required. Throw line or life ring location noted. Solo crossing of water features not permitted.',
                'Low','High');
        }

        // ── Hill/exposed ground ──
        if ($has('terrain','Hill/exposed ground') || $has('terrain','Hills')) {
            $risks[] = self::risk('Difficult Terrain — Hills and Exposed Ground','Operating on hills or exposed high ground','RAYNET Volunteers',
                'Appropriate footwear worn. Operators move carefully in poor or wet conditions. Planned route shared with Group Controller. Map or GPS device carried. No solo off-route movement.',
                'Medium','Medium');
        }

        // ── Scope warnings ──
        if (in_array($get('scope_first_aid'), ['Possibly','Yes'])) {
            $risks[] = self::risk('Out-of-Scope First Aid Tasking','RAYNET asked to provide clinical first aid cover','RAYNET Volunteers',
                'RAYNET does not provide clinical cover unless separately authorised and competent. Role must be clarified with organiser before event acceptance. Appropriate first aid cover confirmed from qualified provider.',
                'Medium','High', true);
        }
        if (in_array($get('scope_casualties'), ['Possibly','Yes'])) {
            $risks[] = self::risk('Out-of-Scope Casualty Management','RAYNET asked to physically manage casualties','RAYNET Volunteers',
                'RAYNET role is communications support only. Do not physically manage casualties unless holding current first aid qualification. Escalate immediately to Event Controller and emergency services.',
                'Medium','High', true);
        }
        if (in_array($get('scope_traffic'), ['Possibly','Yes'])) {
            $risks[] = self::risk('Out-of-Scope Traffic Direction','RAYNET asked to direct or control vehicle traffic','RAYNET Volunteers',
                'RAYNET operators must not direct traffic unless specifically authorised in writing by the relevant highway authority or emergency services. Clarify scope with organiser before acceptance.',
                'Medium','High', true);
        }
        if (in_array($get('scope_marshalling'), ['Possibly','Yes'])) {
            $risks[] = self::risk('Out-of-Scope Crowd Marshalling','RAYNET asked to marshal or control crowds','RAYNET Volunteers',
                'RAYNET role is communications support only. Crowd management is the responsibility of the event organiser. Do not attempt crowd control. Refer all crowd issues to Event Controller.',
                'Medium','Medium', true);
        }
        if (in_array($get('scope_transport'), ['Possibly','Yes'])) {
            $risks[] = self::risk('Out-of-Scope Transport of Non-RAYNET Personnel','RAYNET asked to transport event participants or public','RAYNET Volunteers',
                'RAYNET vehicles are not insured for transporting non-RAYNET personnel in most circumstances. Confirm insurance position before agreeing to this task. Do not transport without explicit authorisation.',
                'Low','High', true);
        }

        // ── No fallback comms — RED escalation per brief ──
        $fallbackArr = array_filter(array_merge($arr('fallback_comms'), array_filter([$get('fallback_methods')])));
        $fallback = array_unique($fallbackArr);
        if (empty($fallback)) {
            $risks[] = self::risk('Loss of Communications — No Fallback Identified','No fallback communication method has been specified','RAYNET Volunteers and Event',
                'A secondary or fallback communication method must be identified before deployment can be approved. Options: secondary radio channel, telephone, manual relay, standby operator. This risk must be resolved before approval.',
                'Medium','High', true);
        } else {
            $fbStr = implode(', ', $fallback);
            $risks[] = self::risk('Loss of Primary Communications','Temporary loss of primary radio communications','RAYNET Volunteers and Event',
                'Fallback communications briefed: '.$fbStr.'. All operators know the fallback procedure. Transition practised before deployment.',
                'Low','Medium');
        }

        // ── Medical emergency — always ──
        $risks[] = self::risk('Medical Emergency','Operator injury or medical episode during deployment','RAYNET Volunteers',
            'First aid kit available and location briefed to all operators. Nearest A&E location confirmed and communicated. 999 always available. First aider identified within team where possible. Medical conditions of operators known to Group Controller.',
            'Low','High');

        // ── Apply proper RAG matrix per brief section 7 ──
        foreach ($risks as &$r) {
            $r['residual'] = self::ragFromMatrix($r['likelihood'], $r['severity']);
        }

        // ── Deduplicate by hazard title ──
        $seen = [];
        $unique = [];
        foreach ($risks as $r) {
            if (!in_array($r['hazard'], $seen)) {
                $seen[] = $r['hazard'];
                $unique[] = $r;
            }
        }
        $risks = $unique;

        // ── Overall RAG ──
        $rag = 'green';
        foreach ($risks as $r) {
            if ($r['residual'] === 'High') { $rag = 'red'; break; }
            if ($r['residual'] === 'Medium') $rag = 'amber';
        }

        return ['risks' => $risks, 'rag' => $rag];
    }

    private static function risk(string $hazard, string $cause, string $persons, string $controls,
                                  string $likelihood, string $severity, bool $escalation = false,
                                  string $briefingNote = ''): array {
        $residual = self::ragFromMatrix($likelihood, $severity);
        return [
            'hazard'              => $hazard,
            'cause'               => $cause,
            'persons_at_risk'     => $persons,
            'controls'            => $controls,
            'likelihood'          => $likelihood,
            'severity'            => $severity,
            'residual'            => $residual,
            'rag'                 => $residual,
            'escalation_required' => $escalation,
            'escalation'          => $escalation,
            'briefing_note'       => $briefingNote,
            'briefingNote'        => $briefingNote,
            'accepted'            => true,
        ];
    }
}
