<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Models\PerformanceTaskAssignment;
use App\Models\PerformanceTaskUpdate;
use App\Models\PerformanceTaskUpdateFile;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerformanceTaskUpdateController extends Controller
{
    public function store(Request $request, PerformanceTaskAssignment $assignment)
    {
        $this->authorizeAssignment($assignment);

        $data = $request->validate([
            'status' => 'required|string',
            'progress_percent' => 'required|integer|min:0|max:100',
            'summary' => 'nullable|string',
            'strategy' => 'nullable|string',
            'blockers' => 'nullable|string',
            'evidence.*' => 'file|max:20480',
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($assignment, $data, $request, $user) {
            $update = PerformanceTaskUpdate::create([
                'task_assignment_id' => $assignment->id,
                'user_id' => $user->id,
                'status' => $data['status'],
                'progress_percent' => $data['progress_percent'],
                'summary' => $data['summary'] ?? null,
                'strategy' => $data['strategy'] ?? null,
                'blockers' => $data['blockers'] ?? null,
                'created_by' => $user->creatorId(),
            ]);

            $files = $request->file('evidence', []);
            if (!is_array($files)) {
                $files = array_filter([$files]);
            }
            foreach ($files as $file) {
                $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $storedName = $fileName . '_' . time() . '_' . uniqid() . '.' . $extension;

                $dir = 'uploads/performance/updates/';
                $tempRequest = new Request();
                $tempRequest->files->set('file', $file);
                $path = Utility::upload_file($tempRequest, 'file', $storedName, $dir, []);
                if ($path['flag'] != 1) {
                    throw new \RuntimeException($path['msg']);
                }

                PerformanceTaskUpdateFile::create([
                    'update_id' => $update->id,
                    'file_name' => $storedName,
                    'file_path' => $path['url'],
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }

            $assignment->progress_percent = $data['progress_percent'];
            $assignment->status = $data['status'];
            $assignment->last_progress_note = $data['summary'] ?? null;
            $assignment->last_progress_at = now();
            $assignment->last_activity_by = $user->id;

            if ($assignment->started_at === null && $data['status'] !== 'pending') {
                $assignment->started_at = now();
            }

            if ($data['status'] === 'completed' && $assignment->completed_at === null) {
                $assignment->completed_at = now();
                if ($assignment->started_at) {
                    $assignment->turnaround_minutes = $assignment->started_at->diffInMinutes($assignment->completed_at);
                }
            }

            $assignment->save();
        });

        return redirect()->back()->with('success', __('Progress updated successfully.'));
    }

    private function authorizeAssignment(PerformanceTaskAssignment $assignment): void
    {
        $user = Auth::user();
        if ($user->can('Manage Performance Pulse')) {
            return;
        }

        abort_unless(
            $user->type === 'employee' &&
            $user->employee &&
            $assignment->employee_id === $user->employee->id,
            403
        );
    }
}

