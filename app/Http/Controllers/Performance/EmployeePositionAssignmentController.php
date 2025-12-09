<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeePositionAssignment;
use App\Models\OrgPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EmployeePositionAssignmentController extends Controller
{
    public function store(Request $request)
    {
        $this->authorizeHierarchy();
        $creatorId = Auth::user()->creatorId();

        $data = $this->validatePayload($request, $creatorId);

        $assignment = EmployeePositionAssignment::create([
            'employee_id' => $data['employee_id'],
            'org_position_id' => $data['org_position_id'],
            'org_role_id' => $this->getRoleId($data['org_position_id']),
            'reports_to_employee_id' => $data['reports_to_employee_id'] ?? null,
            'is_primary' => $data['is_primary'] ?? false,
            'status' => $data['status'] ?? 'active',
            'effective_from' => $data['effective_from'],
            'effective_to' => $data['effective_to'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'assigned_by' => Auth::id(),
            'created_by' => $creatorId,
        ]);

        if ($assignment->is_primary) {
            $this->demoteOtherPrimaries($assignment);
        }

        $this->syncEmployeeColumns($assignment->employee);

        return redirect()->back()->with('success', __('Reporting line updated.'));
    }

    public function update(Request $request, EmployeePositionAssignment $employeePositionAssignment)
    {
        $this->authorizeHierarchy();
        $this->ensureOwnership($employeePositionAssignment);
        $creatorId = Auth::user()->creatorId();

        $data = $this->validatePayload($request, $creatorId, $employeePositionAssignment);

        $employeePositionAssignment->update([
            'org_position_id' => $data['org_position_id'],
            'org_role_id' => $this->getRoleId($data['org_position_id']),
            'reports_to_employee_id' => $data['reports_to_employee_id'] ?? null,
            'is_primary' => $data['is_primary'] ?? $employeePositionAssignment->is_primary,
            'status' => $data['status'] ?? $employeePositionAssignment->status,
            'effective_from' => $data['effective_from'],
            'effective_to' => $data['effective_to'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);

        if ($employeePositionAssignment->is_primary) {
            $this->demoteOtherPrimaries($employeePositionAssignment);
        }

        $this->syncEmployeeColumns($employeePositionAssignment->employee);

        return redirect()->back()->with('success', __('Assignment updated.'));
    }

    public function destroy(EmployeePositionAssignment $employeePositionAssignment)
    {
        $this->authorizeHierarchy();
        $this->ensureOwnership($employeePositionAssignment);

        $employee = $employeePositionAssignment->employee;
        $employeePositionAssignment->delete();
        $this->syncEmployeeColumns($employee);

        return redirect()->back()->with('success', __('Assignment removed.'));
    }

    private function authorizeHierarchy(): void
    {
        abort_unless(Auth::user()->can('Manage Org Hierarchy'), 403);
    }

    private function ensureOwnership(EmployeePositionAssignment $assignment): void
    {
        abort_if($assignment->created_by !== Auth::user()->creatorId(), 403);
    }

    private function validatePayload(Request $request, int $creatorId, ?EmployeePositionAssignment $assignment = null): array
    {
        $employeeRule = Rule::exists('employees', 'id')->where('created_by', $creatorId);

        return $request->validate([
            'employee_id' => [
                $assignment ? 'sometimes' : 'required',
                $employeeRule,
            ],
            'org_position_id' => [
                'required',
                Rule::exists('org_positions', 'id')->where('created_by', $creatorId),
            ],
            'reports_to_employee_id' => [
                'nullable',
                $employeeRule,
            ],
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'status' => 'nullable|string|in:active,on_hold,inactive',
            'is_primary' => 'nullable|boolean',
            'metadata' => 'nullable|array',
        ]);
    }

    private function demoteOtherPrimaries(EmployeePositionAssignment $assignment): void
    {
        EmployeePositionAssignment::where('employee_id', $assignment->employee_id)
            ->where('id', '!=', $assignment->id)
            ->update(['is_primary' => false]);
    }

    private function syncEmployeeColumns(Employee $employee): void
    {
        $primaryAssignment = $employee->positionAssignments()
            ->where('is_primary', true)
            ->orderByDesc('effective_from')
            ->first();

        $employee->org_position_id = $primaryAssignment?->org_position_id;
        $employee->org_role_id = $primaryAssignment?->org_role_id;
        $employee->reports_to_employee_id = $primaryAssignment?->reports_to_employee_id;
        $employee->hierarchy_path = $this->buildHierarchyPath($employee);
        $employee->save();
    }

    private function buildHierarchyPath(Employee $employee): array
    {
        $path = [];
        $current = $employee;

        while ($current) {
            $path[] = $current->id;
            $current = $current->reportsTo;
        }

        return $path;
    }

    private function getRoleId(int $positionId): ?int
    {
        return OrgPosition::find($positionId)?->org_role_id;
    }
}

