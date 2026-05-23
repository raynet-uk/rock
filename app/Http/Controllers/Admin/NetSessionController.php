<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NetSession;
use Illuminate\Http\Request;

class NetSessionController extends Controller {

    public function index() {
        $nets = NetSession::orderBy('name')->get();
        return view('admin.net-sessions.index', compact('nets'));
    }

    public function create() {
        return view('admin.net-sessions.form', ['net' => null]);
    }

    public function store(Request $request) {
        $data = $this->validated($request);
        NetSession::create($data);
        return redirect()->route('admin.net-sessions.index')->with('success', 'Net session created.');
    }

    public function edit(NetSession $netSession) {
        return view('admin.net-sessions.form', ['net' => $netSession]);
    }

    public function update(Request $request, NetSession $netSession) {
        $netSession->update($this->validated($request));
        return redirect()->route('admin.net-sessions.index')->with('success', 'Net session updated.');
    }

    public function destroy(NetSession $netSession) {
        $netSession->delete();
        return back()->with('success', 'Deleted.');
    }

    public function toggleManual(NetSession $netSession) {
        // Turn off all other manual actives first
        NetSession::where('id', '!=', $netSession->id)->update(['manual_active' => false]);
        $netSession->update(['manual_active' => !$netSession->manual_active]);
        return back()->with('success', $netSession->manual_active ? 'Net forced live.' : 'Manual override removed.');
    }

    private function validated(Request $request): array {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'callsign'      => 'required|string|max:100',
            'frequency'     => 'nullable|string|max:50',
            'description'   => 'nullable|string|max:500',
            'controller'    => 'nullable|string|max:100',
            'is_public'     => 'nullable|boolean',
            'is_recurring'  => 'nullable|boolean',
            'days_of_week'  => 'nullable|array',
            'days_of_week.*'=> 'integer|between:0,6',
            'specific_date' => 'nullable|date',
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'nullable|date_format:H:i',
            'active'        => 'nullable|boolean',
            'notes'         => 'nullable|string',
        ]);
        $data['is_public']    = $request->boolean('is_public');
        $data['is_recurring'] = $request->boolean('is_recurring');
        $data['active']       = $request->boolean('active', true);
        if (!$data['is_recurring']) $data['days_of_week'] = null;
        return $data;
    }
}
