<?php
namespace App\Http\Controllers;

use App\Models\EventSupportPack;
use App\Models\EventPost;
use App\Models\EventOperator;
use App\Models\EventGeneratedRisk;
use App\Models\EventPackDocument;
use App\Models\EventUserService;
use App\Models\EventApproval;
use App\Models\User;
use App\Services\RiskEngine;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;

class EventSupportPackController extends Controller {

    public function index() {
        $user  = auth()->user();
        $packs = EventSupportPack::where('user_id', $user->id)
            ->orWhere(fn($q) => $q->whereIn('status',['awaiting_review','escalated']) && $user->isAdmin())
            ->withCount('documents')
            ->with(['risks'])
            ->orderByDesc('event_date')
            ->paginate(20);

        $stats = [
            'upcoming' => EventSupportPack::where('user_id',$user->id)->where('event_date','>=',now())->count(),
            'amber'    => EventSupportPack::where('user_id',$user->id)->where('rag_status','amber')->count(),
            'red'      => EventSupportPack::where('user_id',$user->id)->where('rag_status','red')->count(),
            'help'     => EventSupportPack::where('user_id',$user->id)->where('assistance_visible',true)->count(),
        ];

        return view('event-pack.index', compact('packs','stats'));
    }

    public function create(Request $request) {
        $template   = $request->get('template');
        $cloneFrom  = $request->get('clone');
        $clonePack  = $cloneFrom ? EventSupportPack::find($cloneFrom) : null;
        $templates  = $this->getTemplates();
        $members    = User::role(['member','committee','admin','super-admin'])->orderBy('name')->get(['id','name','callsign']);
        return view('event-pack.wizard', compact('template','clonePack','templates','members'));
    }

    public function store(Request $request) {
        $request->validate(['event_name' => ['required','string','max:200'], 'event_date' => ['required','date']]);

        $data = $request->except(['_token','_method','posts','operators','services']);
        $user = auth()->user();

        // Handle JSON arrays
        $jsonFields = ['raynet_roles','terrain','access_conditions','equipment','facilities','welfare_risks'];
        foreach ($jsonFields as $f) {
            if (isset($data[$f]) && !is_array($data[$f])) $data[$f] = [$data[$f]];
        }

        $pack = EventSupportPack::create(array_merge($data, ['user_id' => $user->id, 'status' => 'draft']));

        // Services
        foreach ($request->input('services', []) as $svc) {
            EventUserService::create(['event_support_pack_id' => $pack->id, 'service_name' => $svc]);
        }

        // Generate risks
        $result = RiskEngine::generateFull($pack->toArray());
        foreach ($result['risks'] as $risk) {
            EventGeneratedRisk::create(array_merge(['event_support_pack_id' => $pack->id], $risk));
        }
        $pack->update(['rag_status' => $result['rag']]);

        AuditLogger::log('event_pack.created', $user, "Created event pack: {$pack->event_name}", ['pack_id' => $pack->id]);

        return response()->json(['success'=>true,'id'=>$pack->id,'rag'=>$result['rag'],'risks'=>$result['risks'],'ragLabel'=>$pack->ragLabel()]);
    }

    public function show(EventSupportPack $eventSupportPack) {
        abort_if($eventSupportPack->user_id !== auth()->id() && !auth()->user()->isAdmin(), 403);
        $eventSupportPack->load(['posts.operators','risks','documents','services','approvals.approver']);
        $members = User::role(['member','committee','admin','super-admin'])->orderBy('name')->get(['id','name','callsign']);
        return view('event-pack.show', compact('eventSupportPack','members'));
    }

    public function update(Request $request, EventSupportPack $eventSupportPack) {
        abort_if($eventSupportPack->user_id !== auth()->id() && !auth()->user()->isAdmin(), 403);
        $data = $request->except(['_token','_method']);
        $jsonFields = ['raynet_roles','terrain','access_conditions','equipment','facilities','welfare_risks'];
        foreach ($jsonFields as $f) {
            if (isset($data[$f]) && !is_array($data[$f])) $data[$f] = [$data[$f]];
        }
        $eventSupportPack->update($data);
        // Regenerate risks
        $eventSupportPack->risks()->delete();
        $result = RiskEngine::generateFull($eventSupportPack->fresh()->toArray());
        foreach ($result['risks'] as $risk) {
            EventGeneratedRisk::create(array_merge(['event_support_pack_id' => $eventSupportPack->id], $risk));
        }
        $eventSupportPack->update(['rag_status' => $result['rag'], 'version' => $eventSupportPack->version + 1]);
        return back()->with('success', 'Event pack updated. Risks regenerated.');
    }

    public function submit(EventSupportPack $eventSupportPack) {
        abort_if($eventSupportPack->user_id !== auth()->id(), 403);
        $eventSupportPack->update(['status' => 'awaiting_review']);
        AuditLogger::log('event_pack.submitted', auth()->user(), "Submitted for review: {$eventSupportPack->event_name}");
        // Notify admins
        $groupName = \App\Helpers\RaynetSetting::groupName();
        $admins = User::role(['admin','super-admin'])->get();
        foreach ($admins as $admin) {
            if ($admin->email) {
                try {
                    \Illuminate\Support\Facades\Mail::send('emails.event-pack-review', [
                        'pack' => $eventSupportPack, 'submitter' => auth()->user(), 'groupName' => $groupName,
                        'url'  => url('/event-pack/'.$eventSupportPack->id),
                    ], function($m) use ($admin, $groupName) {
                        $m->to($admin->email, $admin->name)->subject("Event pack awaiting review — {$groupName}");
                    });
                } catch (\Throwable $e) {}
            }
        }
        return back()->with('success', 'Submitted for review. Administrators have been notified.');
    }

    public function approve(Request $request, EventSupportPack $eventSupportPack) {
        abort_if(!auth()->user()->isAdmin(), 403);
        $rag = $eventSupportPack->rag_status;
        if ($rag === 'red' && !$request->has('force_red')) {
            return back()->withErrors(['error' => 'Red events require Group Controller review before approval.']);
        }
        if ($rag === 'amber') {
            $request->validate(['confirm_amber' => ['required','accepted']], [
                'confirm_amber.required' => 'You must confirm amber controls have been reviewed.',
            ]);
        }
        $status = $rag === 'amber' ? 'approved_with_controls' : 'approved';
        $eventSupportPack->update(['status'=>$status,'approved_by'=>auth()->id(),'approved_at'=>now(),'approval_statement'=>$request->statement]);
        EventApproval::create(['event_support_pack_id'=>$eventSupportPack->id,'status'=>$status,'approver_id'=>auth()->id(),'statement'=>$request->statement,'comments'=>$request->comments]);
        AuditLogger::log('event_pack.approved', $eventSupportPack->user, "Approved: {$eventSupportPack->event_name}");
        return back()->with('success', 'Event pack approved.');
    }

    public function escalate(Request $request, EventSupportPack $eventSupportPack) {
        $eventSupportPack->update(['status' => 'escalated']);
        EventApproval::create(['event_support_pack_id'=>$eventSupportPack->id,'status'=>'escalated','approver_id'=>auth()->id(),'comments'=>$request->comments]);
        AuditLogger::log('event_pack.escalated', $eventSupportPack->user, "Escalated: {$eventSupportPack->event_name}");
        return back()->with('success', 'Escalated to Group Controller.');
    }

    public function return_for_correction(Request $request, EventSupportPack $eventSupportPack) {
        abort_if(!auth()->user()->isAdmin(), 403);
        $eventSupportPack->update(['status' => 'returned']);
        EventApproval::create(['event_support_pack_id'=>$eventSupportPack->id,'status'=>'returned','approver_id'=>auth()->id(),'comments'=>$request->comments]);
        return back()->with('success', 'Returned for correction.');
    }

    public function clone(EventSupportPack $eventSupportPack) {
        abort_if($eventSupportPack->user_id !== auth()->id() && !auth()->user()->isAdmin(), 403);
        $new = $eventSupportPack->replicate();
        $new->status      = 'draft';
        $new->rag_status  = null;
        $new->approved_by = null;
        $new->approved_at = null;
        $new->version     = 1;
        $new->cloned_from = $eventSupportPack->id;
        $new->event_name  = $eventSupportPack->event_name . ' (Copy)';
        $new->save();
        // Clone posts
        foreach ($eventSupportPack->posts as $post) {
            $newPost = $post->replicate();
            $newPost->event_support_pack_id = $new->id;
            $newPost->save();
        }
        return redirect()->route('event-pack.show', $new)->with('success', 'Event cloned. Update the date and details.');
    }

    public function storePost(Request $request, EventSupportPack $eventSupportPack) {
        abort_if($eventSupportPack->user_id !== auth()->id() && !auth()->user()->isAdmin(), 403);
        $request->validate(['post_name' => ['required','string','max:200']]);
        $post = EventPost::create(array_merge(
            $request->except(['_token']),
            ['event_support_pack_id' => $eventSupportPack->id]
        ));
        return response()->json(['success'=>true,'post'=>$post]);
    }

    public function destroyPost(EventPost $post) {
        abort_if($post->pack->user_id !== auth()->id() && !auth()->user()->isAdmin(), 403);
        $post->operators()->delete();
        $post->delete();
        return response()->json(['success'=>true]);
    }

    public function storeOperator(Request $request, EventSupportPack $eventSupportPack) {
        abort_if($eventSupportPack->user_id !== auth()->id() && !auth()->user()->isAdmin(), 403);
        $op = EventOperator::create(array_merge(
            $request->except(['_token']),
            ['event_support_pack_id' => $eventSupportPack->id]
        ));
        if ($op->user_id) {
            $user = User::find($op->user_id);
            if ($user) { $op->update(['name'=>$user->name,'callsign'=>$user->callsign]); }
        }
        return response()->json(['success'=>true,'operator'=>$op->load('user','post')]);
    }

    public function destroyOperator(EventOperator $operator) {
        abort_if($operator->pack->user_id !== auth()->id() && !auth()->user()->isAdmin(), 403);
        $operator->delete();
        return response()->json(['success'=>true]);
    }

    public function generatePdf(EventSupportPack $eventSupportPack, string $type = 'risk') {
        abort_if($eventSupportPack->user_id !== auth()->id() && !auth()->user()->isAdmin(), 403);
        $eventSupportPack->load(['posts.operators','risks','services']);

        require_once app_path('Libraries/fpdf/fpdf.php');
        $pdfService = new \App\Services\EventPackPdfService($eventSupportPack);

        $pdf      = match($type) {
            'operator' => $pdfService->operatorBrief(),
            'assist'   => $pdfService->assistanceRequest(),
            'joining'  => $pdfService->joiningInstructions(),
            default    => $pdfService->riskAssessment(),
        };

        $date     = $eventSupportPack->event_date->format('Ymd');
        $name     = preg_replace('/[^A-Z0-9]/', '_', strtoupper($eventSupportPack->event_name));
        $typeSuffix = match($type) {
            'operator' => 'Operator_Info', 'assist' => 'Assistance_Request',
            'joining'  => 'Joining_Instructions', default => 'Risk_Assessment',
        };
        $filename = "{$date}_{$name}_{$typeSuffix}_v{$eventSupportPack->version}.pdf";

        EventPackDocument::create([
            'event_support_pack_id' => $eventSupportPack->id,
            'document_type'         => $type,
            'filename'              => $filename,
            'version'               => $eventSupportPack->version,
            'generated_at'          => now(),
            'generated_by'          => auth()->id(),
        ]);

        return response($pdf)
            ->header('Content-Type','application/pdf')
            ->header('Content-Disposition','attachment; filename="'.$filename.'"');
    }

    private function getTemplates(): array {
        return [
            'walking'      => ['name'=>'Walking Event','description'=>'Outdoor route, checkpoints, welfare, road exposure'],
            'static'       => ['name'=>'Static Public Event','description'=>'Control point, public interaction, crowd, welfare'],
            'rural'        => ['name'=>'Rural Checkpoint','description'=>'Remote posts, access, weather, lone working'],
            'urban_parade' => ['name'=>'Urban Parade','description'=>'Road exposure, public order, moving event'],
            'training'     => ['name'=>'Training Exercise','description'=>'Internal, defined objectives, lower public exposure'],
            'emergency'    => ['name'=>'Emergency Exercise','description'=>'Multi-agency, formal comms, high documentation'],
            'radio_only'   => ['name'=>'Radio Support Only','description'=>'Comms plan, operator schedule, low physical hazards'],
            'multi_site'   => ['name'=>'Multi-Site Event','description'=>'Talk-through, fallback, outstations, higher coordination'],
        ];
    }
}
