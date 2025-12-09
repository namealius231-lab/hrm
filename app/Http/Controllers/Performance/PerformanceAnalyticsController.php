<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PerformanceAiInsight;
use App\Services\Performance\GeminiInsightService;
use App\Services\Performance\PerformanceAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerformanceAnalyticsController extends Controller
{
    public function __construct(
        private readonly PerformanceAnalyticsService $analyticsService,
        private readonly GeminiInsightService $geminiService
    ) {
    }

    public function index(Request $request)
    {
        $this->authorizeManage();

        $creatorId = Auth::user()->creatorId();
        $employees = Employee::where('created_by', $creatorId)->orderBy('name')->get();
        $departments = Department::where('created_by', $creatorId)->orderBy('name')->get();

        $selectedEmployee = null;
        $selectedEmployeeId = $request->integer('employee_id');
        if ($selectedEmployeeId) {
            $selectedEmployee = $employees->firstWhere('id', $selectedEmployeeId);
        }
        if (!$selectedEmployee && $employees->isNotEmpty()) {
            $selectedEmployee = $employees->first();
        }

        $employeeCharts = $selectedEmployee ? [
            'status' => $this->analyticsService->getTaskStatusDistribution($creatorId, $selectedEmployee->id),
            'timeline' => $this->analyticsService->getTimelineVariance($creatorId, $selectedEmployee->id),
            'productivity' => $this->analyticsService->getProductivityTrend($creatorId, $selectedEmployee->id),
            'efficiency' => $this->analyticsService->getEfficiencyTrend($creatorId, $selectedEmployee->id),
        ] : null;

        $employeeKpis = $selectedEmployee ? $this->analyticsService->getEmployeeKpis($selectedEmployee) : null;
        $employeeInsight = $selectedEmployee
            ? PerformanceAiInsight::where('employee_id', $selectedEmployee->id)->latest()->first()
            : null;
        $employeeInsightMetrics = $selectedEmployee
            ? $this->analyticsService->getEmployeeInsightMetrics($selectedEmployee)
            : null;

        $payload = $this->buildAdminPayload($creatorId);

        return view('performance.pulse.index', array_merge($payload, [
            'employees' => $employees,
            'departments' => $departments,
            'selectedEmployee' => $selectedEmployee,
            'employeeCharts' => $employeeCharts,
            'employeeKpis' => $employeeKpis,
            'employeeInsight' => $employeeInsight,
            'employeeInsightMetrics' => $employeeInsightMetrics,
        ]));
    }

    public function employee()
    {
        $user = Auth::user();
        abort_unless($user->type === 'employee' && $user->employee, 403);

        $employee = $user->employee;
        $creatorId = $user->creatorId();

        $kpis = $this->analyticsService->getEmployeeKpis($employee);

        $charts = [
            'status' => $this->analyticsService->getTaskStatusDistribution($creatorId, $employee->id),
            'timeline' => $this->analyticsService->getTimelineVariance($creatorId, $employee->id),
            'productivity' => $this->analyticsService->getProductivityTrend($creatorId, $employee->id),
            'efficiency' => $this->analyticsService->getEfficiencyTrend($creatorId, $employee->id),
        ];

        $assignments = $employee->performanceAssignments()
            ->with(['task', 'latestUpdate'])
            ->latest('updated_at')
            ->take(8)
            ->get();

        $insight = PerformanceAiInsight::where('employee_id', $employee->id)->latest()->first();
        $insightMetrics = $this->analyticsService->getEmployeeInsightMetrics($employee);

        return view('performance.pulse.employee', compact('employee', 'kpis', 'charts', 'assignments', 'insight', 'insightMetrics'));
    }

    public function chartData(Request $request)
    {
        $user = Auth::user();
        $creatorId = $user->creatorId();
        $employeeId = null;

        if ($user->type === 'employee' && $user->employee) {
            $employeeId = $user->employee->id;
        } elseif ($request->filled('employee_id')) {
            $employeeId = (int) $request->get('employee_id');
        }

        return response()->json([
            'status' => $this->analyticsService->getTaskStatusDistribution($creatorId, $employeeId),
            'timeline' => $this->analyticsService->getTimelineVariance($creatorId, $employeeId),
            'productivity' => $this->analyticsService->getProductivityTrend($creatorId, $employeeId),
            'burnout' => $this->analyticsService->getBurnoutMatrix($creatorId),
            'efficiency' => $this->analyticsService->getEfficiencyTrend($creatorId, $employeeId),
        ]);
    }

    public function generateInsight(Request $request)
    {
        $this->authorizeManage();
        $creatorId = Auth::user()->creatorId();

        $data = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
        ]);

        $employee = Employee::where('created_by', $creatorId)->findOrFail($data['employee_id']);

        $metrics = $this->analyticsService->getEmployeeInsightMetrics($employee);
        $insight = $this->geminiService->generateForEmployee($employee, $metrics, Auth::user());

        if (!$insight) {
            return back()->with('error', __('Unable to generate AI insight. Please confirm your Gemini credentials.'));
        }

        return back()->with('success', __('AI insight generated successfully.'));
    }

    private function authorizeManage(): void
    {
        abort_unless(Auth::user()->can('Manage Performance Pulse'), 403);
    }

    private function buildAdminPayload(int $creatorId): array
    {
        return [
            'kpis' => $this->analyticsService->getAdminKpis($creatorId),
            'statusDistribution' => $this->analyticsService->getTaskStatusDistribution($creatorId),
            'timelineVariance' => $this->analyticsService->getTimelineVariance($creatorId),
            'productivityTrend' => $this->analyticsService->getProductivityTrend($creatorId),
            'burnoutMatrix' => $this->analyticsService->getBurnoutMatrix($creatorId),
            'efficiencyTrend' => $this->analyticsService->getEfficiencyTrend($creatorId),
        ];
    }
}

