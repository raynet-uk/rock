<?php

namespace App\Http\Controllers;

use App\Models\AlertStatus;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class AlertStatusController extends Controller
{
    public function __construct(protected TelegramService $telegram) {}

    public function update(Request $request)
    {
        $data = $request->validate([
            'level'    => 'required|integer|min:1|max:5',
            'headline' => 'nullable|string|max:255',
            'message'  => 'nullable|string',
        ]);

        $status = AlertStatus::query()->first() ?? new AlertStatus();
        $status->fill($data);
        $status->save();

        $this->telegram->sendAlertNotification(
            level:    (int) $data['level'],
            headline: $data['headline'] ?? null,
            message:  $data['message'] ?? null,
        );

        return redirect()
            ->route('admin.dashboard')
            ->with('status', 'Alert level updated.');
    }
}
