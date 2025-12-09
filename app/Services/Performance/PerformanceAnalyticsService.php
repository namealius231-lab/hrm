<?php

namespace App\Services\Performance;

use App\Models\AttendanceEmployee;
use App\Models\Employee;
use App\Models\PerformanceTask;
use App\Models\PerformanceTaskAssignment;
use App\Models\PerformanceTaskUpdate;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PerformanceAnalyticsService
{
    public function getAdminKpis(int $creatorId): array
    {
        $totalEmployees = Employee::where('created_by', $creatorId)->count();
        $activeAssignments = $this->assignmentQuery($creatorId)
            ->whereNotIn('status', ['completed', 'archived', 'cancelled'])
            ->count();

        $today = Carbon::today();
        $presentCount = AttendanceEmployee::where('created_by', $creatorId)
            ->whereDate('date', $today)
            ->count();
        $attendanceRate = $totalEmployees > 0 ? round(($presentCount / $totalEmployees) * 100, 1) : 0;

        $completedAssignments = $this->assignmentQuery($creatorId)->where('status', 'completed')->count();
        $overdueAssignments = $this->assignmentQuery($creatorId)
            ->where('status', '!=', 'completed')
            ->whereHas('task', function ($query) use ($today) {
                $query->whereNotNull('deadline')->where('deadline', '<', $today->endOfDay());
            })
            ->count();

        return [
            'total_employees' => $totalEmployees,
            'active_tasks' => $activeAssignments,
            'attendance_rate' => $attendanceRate,
            'completed_tasks' => $completedAssignments,
            'overdue_tasks' => $overdueAssignments,
        ];
    }

    public function getEmployeeKpis(Employee $employee): array
    {
        $assignments = PerformanceTaskAssignment::where('employee_id', $employee->id);

        $pending = (clone $assignments)->whereIn('status', ['pending', 'in_progress'])->count();
        $completed = (clone $assignments)->where('status', 'completed')->count();
        $overdue = (clone $assignments)
            ->where('status', '!=', 'completed')
            ->whereHas('task', function ($query) {
                $query->whereNotNull('deadline')->where('deadline', '<', Carbon::now());
            })->count();

        $lastUpdate = (clone $assignments)->latest('updated_at')->first();

        return [
            'pending' => $pending,
            'completed' => $completed,
            'overdue' => $overdue,
            'last_activity_at' => optional($lastUpdate)->updated_at,
        ];
    }

    public function getTaskStatusDistribution(int $creatorId, ?int $employeeId = null): array
    {
        $query = $this->assignmentQuery($creatorId);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $raw = $query->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statuses = ['pending', 'in_progress', 'completed', 'blocked', 'overdue', 'archived', 'cancelled'];

        return collect($statuses)->map(fn ($status) => [
            'status' => $status,
            'count' => (int) ($raw[$status] ?? 0),
        ])->values()->all();
    }

    public function getTimelineVariance(int $creatorId, ?int $employeeId = null): array
    {
        $query = $this->assignmentQuery($creatorId)
            ->with(['task'])
            ->whereNotNull('started_at')
            ->whereNotNull('task.deadline')
            ->latest('updated_at')
            ->take(15);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        return $query->get()->map(function (PerformanceTaskAssignment $assignment) {
            $task = $assignment->task;
            $planned = $task->start_date && $task->deadline
                ? Carbon::parse($task->start_date)->diffInHours(Carbon::parse($task->deadline))
                : null;
            $actual = $assignment->started_at && $assignment->completed_at
                ? $assignment->started_at->diffInHours($assignment->completed_at)
                : null;

            return [
                'task' => $task->title,
                'planned_hours' => $planned,
                'actual_hours' => $actual,
                'late' => $actual && $planned ? $actual > $planned : false,
                'completed_at' => $assignment->completed_at?->toDateString(),
            ];
        })->all();
    }

    public function getProductivityTrend(int $creatorId, ?int $employeeId = null): array
    {
        $query = $this->assignmentQuery($creatorId)
            ->whereNotNull('completed_at');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $trend = $query
            ->select(
                DB::raw("DATE_FORMAT(completed_at, '%x-%v') as bucket"),
                DB::raw('COUNT(*) as completed'),
                DB::raw('AVG(progress_percent) as avg_progress')
            )
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->take(12)
            ->get();

        return $trend->map(function ($row) {
            [$year, $week] = explode('-', $row->bucket);
            return [
                'label' => "W{$week} {$year}",
                'completed' => (int) $row->completed,
                'avg_progress' => round((float) $row->avg_progress, 2),
            ];
        })->all();
    }

    public function getBurnoutMatrix(int $creatorId): array
    {
        $windowStart = Carbon::now()->subDays(30);

        $assignments = $this->assignmentQuery($creatorId)
            ->with(['employee'])
            ->where('created_at', '>=', $windowStart)
            ->get()
            ->groupBy('employee_id');

        return $assignments->map(function (Collection $employeeAssignments) {
            $first = $employeeAssignments->first();
            $employee = $first?->employee;

            $completed = $employeeAssignments->where('status', 'completed');
            $avgDays = $completed->avg(function (PerformanceTaskAssignment $assignment) {
                if ($assignment->started_at && $assignment->completed_at) {
                    return $assignment->started_at->diffInDays($assignment->completed_at);
                }
                return null;
            });

            $avgDifficulty = $employeeAssignments->avg(function (PerformanceTaskAssignment $assignment) {
                $difficulty = $assignment->task?->difficulty;
                return $this->difficultyWeight($difficulty);
            });

            return [
                'employee_id' => $employee?->id,
                'employee_name' => $employee?->name,
                'workload' => $employeeAssignments->count(),
                'avg_days' => round($avgDays ?? 0, 2),
                'avg_difficulty' => round($avgDifficulty ?? 0, 2),
            ];
        })->values()->all();
    }

    public function getEfficiencyTrend(int $creatorId, ?int $employeeId = null): array
    {
        $query = $this->assignmentQuery($creatorId)
            ->whereNotNull('completed_at')
            ->with('task');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $rows = $query->get()->groupBy(function (PerformanceTaskAssignment $assignment) {
            return $assignment->completed_at?->format('Y-m');
        });

        return $rows->map(function (Collection $group, $month) {
            $onTime = 0;
            $late = 0;
            foreach ($group as $assignment) {
                $deadline = $assignment->task?->deadline;
                if (!$deadline) {
                    continue;
                }

                if ($assignment->completed_at <= Carbon::parse($deadline)) {
                    $onTime++;
                } else {
                    $late++;
                }
            }
            $total = max(1, $onTime + $late);
            return [
                'month' => $month,
                'on_time_rate' => round(($onTime / $total) * 100, 1),
                'late_rate' => round(($late / $total) * 100, 1),
            ];
        })->values()->all();
    }

    public function getEmployeeInsightMetrics(Employee $employee): array
    {
        $assignments = PerformanceTaskAssignment::where('employee_id', $employee->id)
            ->with('task')
            ->get();

        $totalTasks = $assignments->count();
        $completedTasks = $assignments->where('status', 'completed');
        $pendingTasks = $assignments->whereIn('status', ['pending', 'in_progress', 'blocked']);
        $overdueTasks = $assignments->filter(function (PerformanceTaskAssignment $assignment) {
            $deadline = $assignment->task?->deadline;
            return $assignment->status !== 'completed'
                && $deadline
                && Carbon::now()->greaterThan(Carbon::parse($deadline));
        });

        $avgTurnaroundHours = $completedTasks->avg(function (PerformanceTaskAssignment $assignment) {
            if ($assignment->turnaround_minutes) {
                return round($assignment->turnaround_minutes / 60, 2);
            }

            if ($assignment->started_at && $assignment->completed_at) {
                return round($assignment->started_at->diffInMinutes($assignment->completed_at) / 60, 2);
            }

            return null;
        }) ?? 0;

        $onTimeCompletions = $completedTasks->filter(function (PerformanceTaskAssignment $assignment) {
            $deadline = $assignment->task?->deadline;
            return $deadline
                && $assignment->completed_at
                && $assignment->completed_at->lessThanOrEqualTo(Carbon::parse($deadline));
        })->count();
        $completedCount = max(1, $completedTasks->count());
        $onTimeRate = round(($onTimeCompletions / $completedCount) * 100, 1);

        $averageDifficulty = $assignments->avg(function (PerformanceTaskAssignment $assignment) {
            return $this->difficultyWeight($assignment->task?->difficulty);
        }) ?? 0;

        $recentUpdates = PerformanceTaskUpdate::whereHas('assignment', function ($query) use ($employee) {
            $query->where('employee_id', $employee->id);
        })
            ->latest()
            ->take(3)
            ->get()
            ->map(function (PerformanceTaskUpdate $update) {
                return [
                    'task' => $update->assignment?->task?->title,
                    'status' => $update->status,
                    'progress' => $update->progress_percent,
                    'note' => Str::limit($update->summary, 120),
                    'recorded_at' => optional($update->created_at)->toDateTimeString(),
                ];
            })->all();

        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks->count(),
            'pending_tasks' => $pendingTasks->count(),
            'overdue_tasks' => $overdueTasks->count(),
            'on_time_rate' => $onTimeRate,
            'avg_turnaround_hours' => round($avgTurnaroundHours, 2),
            'average_difficulty' => round($averageDifficulty, 2),
            'recent_updates' => $recentUpdates,
        ];
    }

    private function assignmentQuery(int $creatorId)
    {
        return PerformanceTaskAssignment::query()->where('created_by', $creatorId);
    }

    private function difficultyWeight(?string $difficulty): int
    {
        return match (strtolower($difficulty ?? '')) {
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 2,
        };
    }
}

