<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementEmployee;
use App\Models\Employee;
use App\Models\Meeting;
use App\Models\MeetingEmployee;
use Carbon\Carbon;
use Chatify\Facades\ChatifyMessenger as Chatify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerformanceCommunicationController extends Controller
{
    public function directMessage(Request $request)
    {
        $this->authorizeManage();

        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'message' => 'required|string|max:1000',
        ]);

        $employee = Employee::findOrFail($data['employee_id']);
        $recipientId = $employee->user_id;

        $messageID = mt_rand(9, 999999999) + time();
        Chatify::newMessage([
            'id' => $messageID,
            'type' => 'user',
            'from_id' => Auth::id(),
            'to_id' => $recipientId,
            'body' => htmlentities(trim($data['message']), ENT_QUOTES, 'UTF-8'),
            'attachment' => null,
        ]);

        return back()->with('success', __('Message sent via in-app chat.'));
    }

    public function announcement(Request $request)
    {
        $this->authorizeManage();

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'audience' => 'required|string|in:all,department,custom',
            'department_ids' => 'array',
            'department_ids.*' => 'integer|exists:departments,id',
            'employee_ids' => 'array',
            'employee_ids.*' => 'integer|exists:employees,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $creatorId = Auth::user()->creatorId();
        $employees = $this->resolveAudienceEmployees($data, $creatorId);

        $announcement = Announcement::create([
            'title' => $data['title'],
            'description' => $data['message'],
            'start_date' => $data['start_date'] ?? Carbon::today()->toDateString(),
            'end_date' => $data['end_date'] ?? Carbon::today()->addDays(1)->toDateString(),
            'branch_id' => 0,
            'department_id' => $this->serializeDepartments($data),
            'employee_id' => $this->serializeEmployees($data),
            'created_by' => $creatorId,
        ]);

        foreach ($employees as $employeeId) {
            AnnouncementEmployee::create([
                'announcement_id' => $announcement->id,
                'employee_id' => $employeeId,
                'created_by' => $creatorId,
            ]);
        }

        return back()->with('success', __('Announcement published to selected audience.'));
    }

    public function meeting(Request $request)
    {
        $this->authorizeManage();

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required',
            'note' => 'nullable|string',
            'meeting_link' => 'nullable|string|max:500',
            'audience' => 'required|string|in:all,department,custom',
            'department_ids' => 'array',
            'department_ids.*' => 'integer|exists:departments,id',
            'employee_ids' => 'array',
            'employee_ids.*' => 'integer|exists:employees,id',
        ]);

        $creatorId = Auth::user()->creatorId();
        $employees = $this->resolveAudienceEmployees($data, $creatorId);

        $meeting = Meeting::create([
            'title' => $data['title'],
            'date' => $data['date'],
            'time' => $data['time'],
            'note' => $this->buildMeetingNote($data),
            'branch_id' => 0,
            'department_id' => $this->serializeDepartments($data),
            'employee_id' => $this->serializeEmployees($data),
            'created_by' => $creatorId,
        ]);

        foreach ($employees as $employeeId) {
            MeetingEmployee::create([
                'meeting_id' => $meeting->id,
                'employee_id' => $employeeId,
                'created_by' => $creatorId,
            ]);
        }

        return back()->with('success', __('Meeting scheduled and invitations sent.'));
    }

    private function authorizeManage(): void
    {
        abort_unless(Auth::user()->can('Manage Performance Pulse'), 403);
    }

    private function resolveAudienceEmployees(array $data, int $creatorId)
    {
        return match ($data['audience']) {
            'all' => Employee::where('created_by', $creatorId)->pluck('id'),
            'department' => Employee::where('created_by', $creatorId)
                ->whereIn('department_id', $data['department_ids'] ?? [])
                ->pluck('id'),
            'custom' => collect($data['employee_ids'] ?? []),
        };
    }

    private function serializeDepartments(array $data): string
    {
        if ($data['audience'] === 'department' && !empty($data['department_ids'])) {
            return implode(',', $data['department_ids']);
        }

        return '0';
    }

    private function serializeEmployees(array $data): string
    {
        if ($data['audience'] === 'custom' && !empty($data['employee_ids'])) {
            return implode(',', $data['employee_ids']);
        }

        return '0';
    }

    private function buildMeetingNote(array $data): ?string
    {
        if (empty($data['meeting_link'])) {
            return $data['note'] ?? null;
        }

        $note = $data['note'] ?? '';
        return trim($note . PHP_EOL . 'Meeting link: ' . $data['meeting_link']);
    }
}

