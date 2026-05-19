<?php

namespace Modules\Afteractionreport\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AfterActionReportController extends Controller
{
    public function index()
    {
        $items = DB::table('pxr_reports')
            ->orderByDesc('exercise_date')
            ->paginate(20);

        return view('afteractionreport::admin.index', compact('items'));
    }

    public function show(int $id)
    {
        $item = DB::table('pxr_reports')->where('id', $id)->firstOrFail();
        $item->chronology = json_decode($item->chronology ?? '[]', true);
        $item->lessons    = json_decode($item->lessons    ?? '[]', true);

        return view('afteractionreport::admin.show', compact('item'));
    }

    public function create()
    {
        return view('afteractionreport::admin.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'exercise_name'           => 'required|string|max:255',
            'exercise_date'           => 'required|date',
            'exercise_type'           => 'required|string|max:100',
            'participants'            => 'nullable|string',
            'op_start'                => 'nullable|date_format:Y-m-d\TH:i',
            'op_end'                  => 'nullable|date_format:Y-m-d\TH:i',
            'location'                => 'nullable|string|max:255',
            'author_name'             => 'nullable|string|max:150',
            'author_callsign'         => 'nullable|string|max:50',
            'group_name'              => 'nullable|string|max:150',
            'distribution'            => 'nullable|string|max:500',
            'primary_objective'       => 'nullable|string',
            'jesip_principles_tested' => 'nullable|string',
            'chronology'              => 'nullable|string',
            'jesip_colocation'        => 'nullable|string',
            'jesip_communication'     => 'nullable|string',
            'jesip_coordination'      => 'nullable|string',
            'jesip_joint_risk'        => 'nullable|string',
            'jesip_shared_awareness'  => 'nullable|string',
            'rf_coverage'             => 'nullable|string',
            'equipment_reliability'   => 'nullable|string',
            'logistics'               => 'nullable|string',
            'digital_performance'     => 'nullable|string',
            'lessons'                 => 'nullable|string',
            'overall_grade'           => 'nullable|string|max:50',
            'closing_statement'       => 'nullable|string',
            'signed_by'               => 'nullable|string|max:150',
            'signed_date'             => 'nullable|date',
        ]);

        foreach (['op_start', 'op_end'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = str_replace('T', ' ', $data[$field]) . ':00';
            }
        }

        DB::table('pxr_reports')->insert([
            ...$data,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('admin.afteractionreport.index')
            ->with('success', 'Post-Exercise Report created successfully.');
    }

    public function edit(int $id)
    {
        $item = DB::table('pxr_reports')->where('id', $id)->firstOrFail();
        return view('afteractionreport::admin.form', compact('item'));
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'exercise_name'           => 'required|string|max:255',
            'exercise_date'           => 'required|date',
            'exercise_type'           => 'required|string|max:100',
            'participants'            => 'nullable|string',
            'op_start'                => 'nullable|date_format:Y-m-d\TH:i',
            'op_end'                  => 'nullable|date_format:Y-m-d\TH:i',
            'location'                => 'nullable|string|max:255',
            'author_name'             => 'nullable|string|max:150',
            'author_callsign'         => 'nullable|string|max:50',
            'group_name'              => 'nullable|string|max:150',
            'distribution'            => 'nullable|string',
            'primary_objective'       => 'nullable|string',
            'jesip_principles_tested' => 'nullable|string',
            'chronology'              => 'nullable|string',
            'jesip_colocation'        => 'nullable|string',
            'jesip_communication'     => 'nullable|string',
            'jesip_coordination'      => 'nullable|string',
            'jesip_joint_risk'        => 'nullable|string',
            'jesip_shared_awareness'  => 'nullable|string',
            'rf_coverage'             => 'nullable|string',
            'equipment_reliability'   => 'nullable|string',
            'logistics'               => 'nullable|string',
            'digital_performance'     => 'nullable|string',
            'lessons'                 => 'nullable|string',
            'overall_grade'           => 'nullable|string|max:50',
            'closing_statement'       => 'nullable|string',
            'signed_by'               => 'nullable|string|max:150',
            'signed_date'             => 'nullable|date',
        ]);

        foreach (['op_start', 'op_end'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = str_replace('T', ' ', $data[$field]) . ':00';
            }
        }

        DB::table('pxr_reports')->where('id', $id)->update([
            ...$data,
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('admin.afteractionreport.show', $id)
            ->with('success', 'Report updated successfully.');
    }

    public function destroy(int $id)
    {
        DB::table('pxr_reports')->where('id', $id)->delete();

        return redirect()
            ->route('admin.afteractionreport.index')
            ->with('success', 'Report deleted.');
    }
}
