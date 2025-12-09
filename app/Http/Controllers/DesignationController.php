<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DesignationController extends Controller
{
    public function index()
    {

        if (\Auth::user()->can('Manage Designation')) {
            $user = \Auth::user();
            if ($user->type == 'super admin') {
                $designations = Designation::where(function($query) use ($user) {
                    $query->where('created_by', '=', 0)
                          ->orWhere('created_by', '=', $user->creatorId());
                })->get();
            } else {
                $designations = Designation::where('created_by', '=', $user->creatorId())->get();
            }

            return view('designation.index', compact('designations'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Designation')) {
            $user = \Auth::user();
            if ($user->type == 'super admin') {
                $branchs = Branch::where(function($query) use ($user) {
                    $query->where('created_by', '=', 0)
                          ->orWhere('created_by', '=', $user->creatorId());
                })->get()->pluck('name', 'id');
                $departments = Department::where(function($query) use ($user) {
                    $query->where('created_by', '=', 0)
                          ->orWhere('created_by', '=', $user->creatorId());
                })->get()->pluck('name', 'id');
            } else {
                $branchs = Branch::where('created_by', '=', $user->creatorId())->get()->pluck('name', 'id');
                $departments = Department::where('created_by', '=', $user->creatorId())->get()->pluck('name', 'id');
            }

            return view('designation.create', compact('branchs', 'departments'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {

        if (\Auth::user()->can('Create Designation')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'branch_id' => 'required',
                    'department_id' => 'required',
                    'name' => 'required|max:20',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $user = \Auth::user();
            try {
                if ($user->type == 'super admin') {
                    $department = Department::where('id', $request->department_id)
                        ->where(function($query) use ($user) {
                            $query->where('created_by', '=', 0)
                                  ->orWhere('created_by', '=', $user->creatorId());
                        })->first();
                } else {
                    $department = Department::where('id', $request->department_id)
                        ->where('created_by', '=', $user->creatorId())->first();
                }
                $branch = $department ? $department->branch->id : $request->branch_id;
            } catch (Exception $e) {
                $branch = $request->branch_id;
            }

            $designation                = new Designation();
            $designation->branch_id     = $branch;
            $designation->department_id = $request->department_id;
            $designation->name          = $request->name;
            $designation->created_by    = $user->creatorId();

            $designation->save();

            return redirect()->route('designation.index')->with('success', __('Designation  successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Designation $designation)
    {
        return redirect()->route('designation.index');
    }

    public function edit(Designation $designation)
    {

        if (\Auth::user()->can('Edit Designation')) {
            $user = \Auth::user();
            // Super admin can edit any designation, or check created_by for others
            if ($user->type == 'super admin' || $designation->created_by == $user->creatorId() || $designation->created_by == 0) {
                if ($user->type == 'super admin') {
                    $branchs = Branch::where(function($query) use ($user) {
                        $query->where('created_by', '=', 0)
                              ->orWhere('created_by', '=', $user->creatorId());
                    })->get()->pluck('name', 'id');
                    $departments = Department::where(function($query) use ($user) {
                        $query->where('created_by', '=', 0)
                              ->orWhere('created_by', '=', $user->creatorId());
                    })->get()->pluck('name', 'id');
                } else {
                    if (!empty($designation->branch_id)) {
                        $branchs = Branch::where('id', $designation->branch_id)->first()->pluck('name', 'id');
                    } else {
                        $branchs = Branch::where('created_by', '=', $user->creatorId())->get()->pluck('name', 'id');
                    }
                    $departments = Department::where('id', $designation->department_id)->first()->pluck('name', 'id');
                }

                return view('designation.edit', compact('designation', 'departments', 'branchs'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Designation $designation)
    {
        if (\Auth::user()->can('Edit Designation')) {
            $user = \Auth::user();
            // Super admin can update any designation, or check created_by for others
            if ($user->type == 'super admin' || $designation->created_by == $user->creatorId() || $designation->created_by == 0) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'branch_id' => 'required',
                        'department_id' => 'required',
                        'name' => 'required|max:20',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                try {
                    if ($user->type == 'super admin') {
                        $department = Department::where('id', $request->department_id)
                            ->where(function($query) use ($user) {
                                $query->where('created_by', '=', 0)
                                      ->orWhere('created_by', '=', $user->creatorId());
                            })->first();
                    } else {
                        $department = Department::where('id', $request->department_id)
                            ->where('created_by', '=', $user->creatorId())->first();
                    }
                    $branch = $department ? $department->branch->id : $request->branch_id;
                } catch (Exception $e) {
                    $branch = $request->branch_id;
                }

                $designation->name          = $request->name;
                $designation->branch_id     = $branch;
                $designation->department_id = $request->department_id;
                $designation->save();

                return redirect()->route('designation.index')->with('success', __('Designation  successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Designation $designation)
    {
        if (\Auth::user()->can('Delete Designation')) {
            $user = \Auth::user();
            // Super admin can delete any designation, or check created_by for others
            if ($user->type == 'super admin' || $designation->created_by == $user->creatorId() || $designation->created_by == 0) {
                $designation->delete();

                return redirect()->route('designation.index')->with('success', __('Designation successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
