<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('Manage Department'))
        {
            $user = \Auth::user();
            if ($user->type == 'super admin') {
                $departments = Department::where(function($query) use ($user) {
                    $query->where('created_by', '=', 0)
                          ->orWhere('created_by', '=', $user->creatorId());
                })->with('branch')->get();
            } else {
                $departments = Department::where('created_by', '=', $user->creatorId())->with('branch')->get();
            }

            return view('department.index', compact('departments'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('Create Department'))
        {
            $user = \Auth::user();
            if ($user->type == 'super admin') {
                $branch = Branch::where(function($query) use ($user) {
                    $query->where('created_by', '=', 0)
                          ->orWhere('created_by', '=', $user->creatorId());
                })->get()->pluck('name', 'id');
            } else {
                $branch = Branch::where('created_by', $user->creatorId())->get()->pluck('name', 'id');
            }

            return view('department.create', compact('branch'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('Create Department'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'branch_id' => 'required',
                                   'name' => 'required|max:20',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $department             = new Department();
            $department->branch_id  = $request->branch_id;
            $department->name       = $request->name;
            $department->created_by = \Auth::user()->creatorId();
            $department->save();

            return redirect()->route('department.index')->with('success', __('Department  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Department $department)
    {
        return redirect()->route('department.index');
    }

    public function edit(Department $department)
    {
        if(\Auth::user()->can('Edit Department'))
        {
            $user = \Auth::user();
            // Super admin can edit any department, or check created_by for others
            if($user->type == 'super admin' || $department->created_by == $user->creatorId() || $department->created_by == 0)
            {
                if ($user->type == 'super admin') {
                    $branch = Branch::where(function($query) use ($user) {
                        $query->where('created_by', '=', 0)
                              ->orWhere('created_by', '=', $user->creatorId());
                    })->get()->pluck('name', 'id');
                } else {
                    $branch = Branch::where('created_by', $user->creatorId())->get()->pluck('name', 'id');
                }

                return view('department.edit', compact('department', 'branch'));
            }
            else
            {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Department $department)
    {
        if(\Auth::user()->can('Edit Department'))
        {
            $user = \Auth::user();
            // Super admin can update any department, or check created_by for others
            if($user->type == 'super admin' || $department->created_by == $user->creatorId() || $department->created_by == 0)
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'branch_id' => 'required',
                                       'name' => 'required|max:20',
                                   ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $department->branch_id = $request->branch_id;
                $department->name      = $request->name;
                $department->save();

                return redirect()->route('department.index')->with('success', __('Department successfully updated.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Department $department)
    {
        if(\Auth::user()->can('Delete Department'))
        {
            $user = \Auth::user();
            // Super admin can delete any department, or check created_by for others
            if($user->type == 'super admin' || $department->created_by == $user->creatorId() || $department->created_by == 0)
            {
                $employee     = Employee::where('department_id',$department->id)->get();
                if(count($employee) == 0)
                {
                    Designation::where('department_id',$department->id)->delete();
                    $department->delete();
                }
                else
                {
                    return redirect()->route('department.index')->with('error', __('This department has employees. Please remove the employee from this department.'));
                }

                return redirect()->route('department.index')->with('success', __('Department successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
