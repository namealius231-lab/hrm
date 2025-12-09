<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Designation;
use App\Models\OrgPosition;
use App\Models\OrgRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrgPositionController extends Controller
{
    public function index()
    {
        $this->authorizeHierarchy();
        $creatorId = Auth::user()->creatorId();

        $positions = OrgPosition::where('created_by', $creatorId)
            ->with(['role', 'department', 'designation', 'parent', 'assignments.employee'])
            ->orderBy('level')
            ->get();

        $roles = OrgRole::where('created_by', $creatorId)->orderBy('name')->get();
        $departments = Department::where('created_by', $creatorId)->orderBy('name')->get();
        $designations = Designation::where('created_by', $creatorId)->orderBy('name')->get();
        $employees = Employee::where('created_by', $creatorId)->orderBy('name')->get();

        return view('org_structure.positions.index', compact('positions', 'roles', 'departments', 'designations', 'employees'));
    }

    public function store(Request $request)
    {
        $this->authorizeHierarchy();
        $creatorId = Auth::user()->creatorId();

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('org_positions', 'code')->where('created_by', $creatorId),
            ],
            'org_role_id' => [
                'required',
                Rule::exists('org_roles', 'id')->where('created_by', $creatorId),
            ],
            'department_id' => [
                'nullable',
                Rule::exists('departments', 'id')->where('created_by', $creatorId),
            ],
            'designation_id' => [
                'nullable',
                Rule::exists('designations', 'id')->where('created_by', $creatorId),
            ],
            'reports_to_position_id' => [
                'nullable',
                Rule::exists('org_positions', 'id')->where('created_by', $creatorId),
            ],
            'level' => 'nullable|integer|min:1|max:20',
            'headcount' => 'nullable|integer|min:1|max:1000',
            'band' => 'nullable|string|max:50',
            'responsibilities' => 'nullable|string',
        ]);

        OrgPosition::create([
            'title' => $data['title'],
            'code' => $data['code'],
            'org_role_id' => $data['org_role_id'],
            'department_id' => $data['department_id'] ?? null,
            'designation_id' => $data['designation_id'] ?? null,
            'reports_to_position_id' => $data['reports_to_position_id'] ?? null,
            'level' => $data['level'] ?? 1,
            'headcount' => $data['headcount'] ?? 1,
            'band' => $data['band'] ?? null,
            'responsibilities' => $data['responsibilities'] ?? null,
            'created_by' => $creatorId,
        ]);

        return redirect()->route('org-positions.index')->with('success', __('Position created.'));
    }

    public function update(Request $request, OrgPosition $orgPosition)
    {
        $this->authorizeHierarchy();
        $this->ensureOwnership($orgPosition);
        $creatorId = Auth::user()->creatorId();

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('org_positions', 'code')
                    ->ignore($orgPosition->id)
                    ->where('created_by', $creatorId),
            ],
            'org_role_id' => [
                'required',
                Rule::exists('org_roles', 'id')->where('created_by', $creatorId),
            ],
            'department_id' => [
                'nullable',
                Rule::exists('departments', 'id')->where('created_by', $creatorId),
            ],
            'designation_id' => [
                'nullable',
                Rule::exists('designations', 'id')->where('created_by', $creatorId),
            ],
            'reports_to_position_id' => [
                'nullable',
                Rule::exists('org_positions', 'id')->where('created_by', $creatorId),
            ],
            'level' => 'nullable|integer|min:1|max:20',
            'headcount' => 'nullable|integer|min:1|max:1000',
            'band' => 'nullable|string|max:50',
            'responsibilities' => 'nullable|string',
        ]);

        $orgPosition->update([
            'title' => $data['title'],
            'code' => $data['code'],
            'org_role_id' => $data['org_role_id'],
            'department_id' => $data['department_id'] ?? null,
            'designation_id' => $data['designation_id'] ?? null,
            'reports_to_position_id' => $data['reports_to_position_id'] ?? null,
            'level' => $data['level'] ?? $orgPosition->level,
            'headcount' => $data['headcount'] ?? $orgPosition->headcount,
            'band' => $data['band'] ?? $orgPosition->band,
            'responsibilities' => $data['responsibilities'] ?? null,
        ]);

        return redirect()->route('org-positions.index')->with('success', __('Position updated.'));
    }

    public function destroy(OrgPosition $orgPosition)
    {
        $this->authorizeHierarchy();
        $this->ensureOwnership($orgPosition);

        abort_if($orgPosition->children()->exists(), 400, __('Detach child positions first.'));
        abort_if($orgPosition->assignments()->exists(), 400, __('Unassign employees before deleting.'));

        $orgPosition->delete();

        return redirect()->route('org-positions.index')->with('success', __('Position removed.'));
    }

    private function authorizeHierarchy(): void
    {
        abort_unless(Auth::user()->can('Manage Org Hierarchy'), 403);
    }

    private function ensureOwnership(OrgPosition $position): void
    {
        abort_if($position->created_by !== Auth::user()->creatorId(), 403);
    }
}

