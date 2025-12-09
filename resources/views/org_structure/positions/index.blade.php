@extends('layouts.admin')

@section('page-title')
    {{ __('Org Positions') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Org Structure') }}</li>
    <li class="breadcrumb-item">{{ __('Positions') }}</li>
@endsection

@section('content')
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Position Directory') }}</h5>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Role') }}</th>
                            <th>{{ __('Department') }}</th>
                            <th>{{ __('Reports To') }}</th>
                            <th>{{ __('Assignments') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($positions as $position)
                            <tr>
                                <td>
                                    <strong>{{ $position->title }}</strong>
                                    <div class="text-muted small">{{ $position->code }}</div>
                                </td>
                                <td>{{ optional($position->role)->name ?? '—' }}</td>
                                <td>{{ optional($position->department)->name ?? '—' }}</td>
                                <td>{{ optional($position->parent)->title ?? '—' }}</td>
                                <td>
                                    @forelse ($position->assignments as $assignment)
                                        <span class="badge bg-light text-dark mb-1">{{ optional($assignment->employee)->name }}</span>
                                    @empty
                                        <span class="text-muted small">{{ __('Unassigned') }}</span>
                                    @endforelse
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#edit-position-{{ $position->id }}">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    {!! Form::open(['method' => 'DELETE', 'route' => ['org-positions.destroy', $position], 'class' => 'd-inline']) !!}
                                    <button type="submit" class="btn btn-sm btn-light text-danger"
                                        onclick="return confirm('{{ __('Delete this position?') }}')">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                            <tr class="collapse" id="edit-position-{{ $position->id }}">
                                <td colspan="6">
                                    {!! Form::model($position, ['route' => ['org-positions.update', $position], 'method' => 'PUT', 'class' => 'border rounded p-3 bg-light-subtle']) !!}
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            {!! Form::label('title', __('Title'), ['class' => 'form-label']) !!}
                                            {!! Form::text('title', null, ['class' => 'form-control', 'required' => true]) !!}
                                        </div>
                                        <div class="col-md-4">
                                            {!! Form::label('code', __('Code'), ['class' => 'form-label']) !!}
                                            {!! Form::text('code', null, ['class' => 'form-control', 'required' => true]) !!}
                                        </div>
                                        <div class="col-md-4">
                                            {!! Form::label('org_role_id', __('Role'), ['class' => 'form-label']) !!}
                                            <select name="org_role_id" class="form-select" required>
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->id }}" @selected($position->org_role_id == $role->id)>
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            {!! Form::label('department_id', __('Department'), ['class' => 'form-label']) !!}
                                            <select name="department_id" class="form-select">
                                                <option value="">{{ __('—') }}</option>
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->id }}" @selected($position->department_id == $department->id)>
                                                        {{ $department->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            {!! Form::label('designation_id', __('Designation'), ['class' => 'form-label']) !!}
                                            <select name="designation_id" class="form-select">
                                                <option value="">{{ __('—') }}</option>
                                                @foreach ($designations as $designation)
                                                    <option value="{{ $designation->id }}" @selected($position->designation_id == $designation->id)>
                                                        {{ $designation->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            {!! Form::label('reports_to_position_id', __('Reports To'), ['class' => 'form-label']) !!}
                                            <select name="reports_to_position_id" class="form-select">
                                                <option value="">{{ __('—') }}</option>
                                                @foreach ($positions as $parent)
                                                    @if ($parent->id !== $position->id)
                                                        <option value="{{ $parent->id }}" @selected($position->reports_to_position_id == $parent->id)>
                                                            {{ $parent->title }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            {!! Form::label('level', __('Level'), ['class' => 'form-label']) !!}
                                            {!! Form::number('level', null, ['class' => 'form-control', 'min' => 1]) !!}
                                        </div>
                                        <div class="col-md-4">
                                            {!! Form::label('headcount', __('Headcount'), ['class' => 'form-label']) !!}
                                            {!! Form::number('headcount', null, ['class' => 'form-control', 'min' => 1]) !!}
                                        </div>
                                        <div class="col-md-4">
                                            {!! Form::label('band', __('Band'), ['class' => 'form-label']) !!}
                                            {!! Form::text('band', null, ['class' => 'form-control']) !!}
                                        </div>
                                        <div class="col-12">
                                            {!! Form::label('responsibilities', __('Responsibilities'), ['class' => 'form-label']) !!}
                                            {!! Form::textarea('responsibilities', null, ['class' => 'form-control', 'rows' => 2]) !!}
                                        </div>
                                    </div>
                                    <div class="text-end mt-3">
                                        <button type="submit" class="btn btn-sm btn-primary">{{ __('Save') }}</button>
                                    </div>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <p class="text-center text-muted mb-0">{{ __('No positions yet. Use the form to the right to create one.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Create Position') }}</h5>
            </div>
            <div class="card-body">
                {!! Form::open(['route' => 'org-positions.store', 'method' => 'POST']) !!}
                <div class="mb-2">
                    {!! Form::label('title', __('Title'), ['class' => 'form-label']) !!}
                    {!! Form::text('title', null, ['class' => 'form-control', 'required' => true]) !!}
                </div>
                <div class="mb-2">
                    {!! Form::label('code', __('Code'), ['class' => 'form-label']) !!}
                    {!! Form::text('code', null, ['class' => 'form-control', 'required' => true]) !!}
                </div>
                <div class="mb-2">
                    {!! Form::label('org_role_id', __('Linked Role'), ['class' => 'form-label']) !!}
                    <select name="org_role_id" class="form-select" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    {!! Form::label('department_id', __('Department'), ['class' => 'form-label']) !!}
                    <select name="department_id" class="form-select">
                        <option value="">{{ __('—') }}</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    {!! Form::label('designation_id', __('Designation'), ['class' => 'form-label']) !!}
                    <select name="designation_id" class="form-select">
                        <option value="">{{ __('—') }}</option>
                        @foreach ($designations as $designation)
                            <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    {!! Form::label('reports_to_position_id', __('Reports To'), ['class' => 'form-label']) !!}
                    <select name="reports_to_position_id" class="form-select">
                        <option value="">{{ __('—') }}</option>
                        @foreach ($positions as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        {!! Form::label('level', __('Level'), ['class' => 'form-label']) !!}
                        {!! Form::number('level', 1, ['class' => 'form-control', 'min' => 1]) !!}
                    </div>
                    <div class="col-md-6">
                        {!! Form::label('headcount', __('Headcount'), ['class' => 'form-label']) !!}
                        {!! Form::number('headcount', 1, ['class' => 'form-control', 'min' => 1]) !!}
                    </div>
                </div>
                <div class="mb-2">
                    {!! Form::label('band', __('Band'), ['class' => 'form-label']) !!}
                    {!! Form::text('band', null, ['class' => 'form-control']) !!}
                </div>
                <div class="mb-3">
                    {!! Form::label('responsibilities', __('Responsibilities'), ['class' => 'form-label']) !!}
                    {!! Form::textarea('responsibilities', null, ['class' => 'form-control', 'rows' => 2]) !!}
                </div>
                <button type="submit" class="btn btn-primary w-100">{{ __('Create Position') }}</button>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Assign Employee') }}</h5>
            </div>
            <div class="card-body">
                {!! Form::open(['route' => 'employee-position-assignments.store', 'method' => 'POST']) !!}
                <div class="mb-2">
                    {!! Form::label('employee_id', __('Employee'), ['class' => 'form-label']) !!}
                    <select name="employee_id" class="form-select" required>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    {!! Form::label('org_position_id', __('Position'), ['class' => 'form-label']) !!}
                    <select name="org_position_id" class="form-select" required>
                        @foreach ($positions as $position)
                            <option value="{{ $position->id }}">{{ $position->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    {!! Form::label('reports_to_employee_id', __('Reports To Employee'), ['class' => 'form-label']) !!}
                    <select name="reports_to_employee_id" class="form-select">
                        <option value="">{{ __('—') }}</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        {!! Form::label('effective_from', __('Effective From'), ['class' => 'form-label']) !!}
                        <input type="date" name="effective_from" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        {!! Form::label('effective_to', __('Effective To'), ['class' => 'form-label']) !!}
                        <input type="date" name="effective_to" class="form-control">
                    </div>
                </div>
                <div class="mb-2 mt-2">
                    {!! Form::label('status', __('Status'), ['class' => 'form-label']) !!}
                    {!! Form::select('status', ['active' => __('Active'), 'on_hold' => __('On hold'), 'inactive' => __('Inactive')], 'active', ['class' => 'form-select']) !!}
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_primary" value="1" id="primaryAssignment" checked>
                    <label class="form-check-label" for="primaryAssignment">{{ __('Set as primary role') }}</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">{{ __('Assign Employee') }}</button>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection

