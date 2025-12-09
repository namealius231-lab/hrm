<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Models\OrgRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrgRoleController extends Controller
{
    public function index()
    {
        $this->authorizeHierarchy();
        $creatorId = Auth::user()->creatorId();

        $roles = OrgRole::where('created_by', $creatorId)
            ->with('parent')
            ->orderBy('rank_weight')
            ->get();

        return view('org_structure.roles.index', compact('roles'));
    }

    public function create()
    {
        $this->authorizeHierarchy();
        $creatorId = Auth::user()->creatorId();
        $roles = OrgRole::where('created_by', $creatorId)->orderBy('name')->get();

        return view('org_structure.roles.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->authorizeHierarchy();
        $creatorId = Auth::user()->creatorId();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('org_roles', 'code')->where('created_by', $creatorId),
            ],
            'reports_to_role_id' => [
                'nullable',
                Rule::exists('org_roles', 'id')->where('created_by', $creatorId),
            ],
            'level' => 'nullable|integer|min:1|max:20',
            'rank_weight' => 'nullable|integer|min:1|max:1000',
            'is_executive' => 'nullable|boolean',
            'responsibilities' => 'nullable|string',
        ]);

        OrgRole::create([
            'name' => $data['name'],
            'code' => $data['code'],
            'reports_to_role_id' => $data['reports_to_role_id'] ?? null,
            'level' => $data['level'] ?? 1,
            'rank_weight' => $data['rank_weight'] ?? 100,
            'is_executive' => $data['is_executive'] ?? false,
            'responsibilities' => $data['responsibilities'] ?? null,
            'created_by' => $creatorId,
        ]);

        return redirect()->route('org-roles.index')->with('success', __('Role created.'));
    }

    public function edit(OrgRole $orgRole)
    {
        $this->authorizeHierarchy();
        $this->ensureOwnership($orgRole);

        $creatorId = Auth::user()->creatorId();
        $roles = OrgRole::where('created_by', $creatorId)
            ->where('id', '!=', $orgRole->id)
            ->orderBy('name')
            ->get();

        return view('org_structure.roles.edit', compact('orgRole', 'roles'));
    }

    public function update(Request $request, OrgRole $orgRole)
    {
        $this->authorizeHierarchy();
        $this->ensureOwnership($orgRole);
        $creatorId = Auth::user()->creatorId();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('org_roles', 'code')
                    ->ignore($orgRole->id)
                    ->where('created_by', $creatorId),
            ],
            'reports_to_role_id' => [
                'nullable',
                Rule::exists('org_roles', 'id')->where('created_by', $creatorId),
            ],
            'level' => 'nullable|integer|min:1|max:20',
            'rank_weight' => 'nullable|integer|min:1|max:1000',
            'is_executive' => 'nullable|boolean',
            'responsibilities' => 'nullable|string',
        ]);

        $orgRole->update([
            'name' => $data['name'],
            'code' => $data['code'],
            'reports_to_role_id' => $data['reports_to_role_id'] ?? null,
            'level' => $data['level'] ?? $orgRole->level,
            'rank_weight' => $data['rank_weight'] ?? $orgRole->rank_weight,
            'is_executive' => $data['is_executive'] ?? $orgRole->is_executive,
            'responsibilities' => $data['responsibilities'] ?? null,
        ]);

        return redirect()->route('org-roles.index')->with('success', __('Role updated.'));
    }

    public function destroy(OrgRole $orgRole)
    {
        $this->authorizeHierarchy();
        $this->ensureOwnership($orgRole);

        abort_if($orgRole->children()->exists(), 400, __('Detach child roles first.'));
        abort_if($orgRole->positions()->exists(), 400, __('Detach positions before deleting.'));

        $orgRole->delete();

        return redirect()->route('org-roles.index')->with('success', __('Role removed.'));
    }

    private function authorizeHierarchy(): void
    {
        abort_unless(Auth::user()->can('Manage Org Hierarchy'), 403);
    }

    private function ensureOwnership(OrgRole $role): void
    {
        abort_if($role->created_by !== Auth::user()->creatorId(), 403);
    }
}

