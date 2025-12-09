<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PerformanceTask;
use App\Models\PerformanceTaskAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerformanceTaskController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $creatorId = $user->creatorId();

        $query = PerformanceTask::with(['owner'])
            ->withCount('assignments')
            ->latest('deadline');

        if ($user->type === 'employee' && $user->employee) {
            $employeeId = $user->employee->id;
            $query->whereHas('assignments', fn($q) => $q->where('employee_id', $employeeId));
        } else {
            $query->where('created_by', $creatorId);
        }

        if ($search = request('search')) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        $tasks = $query->paginate(15);

        $employees = Employee::where('created_by', $creatorId)
            ->orderBy('name')
            ->get();

        return view('performance.tasks.index', compact('tasks', 'employees'));
    }

    public function create()
    {
        $this->authorizeManage();
        $creatorId = Auth::user()->creatorId();
        $employees = Employee::where('created_by', $creatorId)->orderBy('name')->get();

        return view('performance.tasks.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $this->authorizeManage();

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'required|string|in:low,medium,high',
            'priority' => 'required|string|in:low,normal,high,critical',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'expected_hours' => 'nullable|integer|min:0',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $creatorId = Auth::user()->creatorId();

        DB::transaction(function () use ($data, $creatorId) {
            $task = PerformanceTask::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'difficulty' => $data['difficulty'],
                'priority' => $data['priority'],
                'status' => 'pending',
                'visibility' => 'private',
                'auto_overdue' => true,
                'start_date' => $data['start_date'] ?? null,
                'deadline' => $data['deadline'] ?? null,
                'expected_hours' => $data['expected_hours'] ?? 0,
                'assigned_by' => Auth::id(),
                'created_by' => $creatorId,
                'metadata' => [
                    'source' => 'manual',
                ],
            ]);

            $assignments = collect($data['employee_ids'])->map(function ($employeeId) use ($task, $creatorId) {
                return [
                    'task_id' => $task->id,
                    'employee_id' => $employeeId,
                    'status' => 'pending',
                    'progress_percent' => 0,
                    'created_by' => $creatorId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            PerformanceTaskAssignment::insert($assignments);
        });

        return redirect()->route('performance-tasks.index')->with('success', __('Task created and assigned successfully.'));
    }

    public function show(PerformanceTask $performanceTask)
    {
        $user = Auth::user();
        $creatorId = $user->creatorId();

        if ($user->type !== 'employee') {
            $this->authorizeViewCompanyTask($performanceTask, $creatorId);
        } else {
            $this->authorizeEmployeeTask($performanceTask, $user->employee->id);
        }

        $performanceTask->load([
            'assignments' => function ($query) {
                $query->with([
                    'employee',
                    'latestUpdate',
                    'updates' => fn($update) => $update->latest()->with('files')->take(10),
                    'reviews' => fn($review) => $review->latest(),
                ]);
            },
            'owner',
        ]);

        return view('performance.tasks.show', [
            'task' => $performanceTask,
        ]);
    }

    public function edit(PerformanceTask $performanceTask)
    {
        $this->authorizeManage();
        $this->authorizeViewCompanyTask($performanceTask, Auth::user()->creatorId());

        $employees = Employee::where('created_by', Auth::user()->creatorId())->orderBy('name')->get();

        $assigned = $performanceTask->assignments()->pluck('employee_id')->toArray();

        return view('performance.tasks.edit', compact('performanceTask', 'employees', 'assigned'));
    }

    public function update(Request $request, PerformanceTask $performanceTask)
    {
        $this->authorizeManage();
        $this->authorizeViewCompanyTask($performanceTask, Auth::user()->creatorId());

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'required|string|in:low,medium,high',
            'priority' => 'required|string|in:low,normal,high,critical',
            'status' => 'required|string',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'expected_hours' => 'nullable|integer|min:0',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        DB::transaction(function () use ($performanceTask, $data) {
            $performanceTask->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'difficulty' => $data['difficulty'],
                'priority' => $data['priority'],
                'status' => $data['status'],
                'start_date' => $data['start_date'] ?? null,
                'deadline' => $data['deadline'] ?? null,
                'expected_hours' => $data['expected_hours'] ?? 0,
            ]);

            $currentAssignments = $performanceTask->assignments()->pluck('employee_id')->toArray();
            $incoming = $data['employee_ids'];

            $toDetach = array_diff($currentAssignments, $incoming);
            $toAttach = array_diff($incoming, $currentAssignments);

            if (!empty($toDetach)) {
                PerformanceTaskAssignment::where('task_id', $performanceTask->id)
                    ->whereIn('employee_id', $toDetach)
                    ->delete();
            }

            foreach ($toAttach as $employeeId) {
                PerformanceTaskAssignment::create([
                    'task_id' => $performanceTask->id,
                    'employee_id' => $employeeId,
                    'status' => 'pending',
                    'progress_percent' => 0,
                    'created_by' => Auth::user()->creatorId(),
                ]);
            }
        });

        return redirect()->route('performance-tasks.show', $performanceTask)->with('success', __('Task updated successfully.'));
    }

    public function destroy(PerformanceTask $performanceTask)
    {
        $this->authorizeManage();
        $this->authorizeViewCompanyTask($performanceTask, Auth::user()->creatorId());

        $performanceTask->assignments()->delete();
        $performanceTask->delete();

        return redirect()->route('performance-tasks.index')->with('success', __('Task deleted.'));
    }

    private function authorizeManage(): void
    {
        abort_unless(Auth::user()->can('Manage Performance Pulse'), 403);
    }

    private function authorizeViewCompanyTask(PerformanceTask $task, int $creatorId): void
    {
        abort_if($task->created_by !== $creatorId, 403);
    }

    private function authorizeEmployeeTask(PerformanceTask $task, int $employeeId): void
    {
        abort_unless(
            $task->assignments()->where('employee_id', $employeeId)->exists(),
            403
        );
    }
}

