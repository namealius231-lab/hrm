@php
    $chatgpt = App\Models\Utility::getValByName('enable_chatgpt');
@endphp

{{ Form::model($announcement, ['route' => ['announcement.update', $announcement->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">

    @if ($chatgpt == 'on')
        <div class="card-footer text-end">
            <a href="#" class="btn btn-sm btn-primary" data-size="medium" data-ajax-popup-over="true"
                data-url="{{ route('generate', ['announcement']) }}" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Generate') }}" data-title="{{ __('Generate Content With AI') }}">
                <i class="fas fa-robot"></i>{{ __(' Generate With AI') }}
            </a>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('title', __('Announcement Title'), ['class' => 'col-form-label']) }}<x-required></x-required>
                {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Announcement Title')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('branch_id', __('Branch'), ['class' => 'col-form-label']) }}<x-required></x-required>

                {{ Form::select('branch_id', $branch, null, ['class' => 'form-control select2']) }}
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('department_id', __('Department'), ['class' => 'col-form-label']) }}<x-required></x-required>

                <div class="department_div">
                    {{-- <select class="form-control select2  department_id" id="department_id" name="department_id[]"
                         placeholder="Select Department" multiple>
                    </select> --}}

                    {{-- {{ Form::select('question[]', $job_question, (!empty($company_job->question)) ? explode(',', $company_job->question) : null, array('class' => 'form-control','multiple','data-toggle'=>'select')) }} --}}
                    {{ Form::select('department_id[]', $departments, !empty($announcement->department_id) ? explode(',', $announcement->department_id) : null, ['class' => 'form-control select2 department_id', 'id' => 'department_id']) }}
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}<x-required></x-required>
                {{ Form::select('employee_id[]', $employees, !empty($announcement->employee_id) ? explode(',', $announcement->employee_id) : null, ['class' => 'form-control select2 employee_id', 'id' => 'employee_id', 'multiple' => 'multiple']) }}
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Announcement start Date'), ['class' => 'col-form-label']) }}<x-required></x-required>
                {{ Form::text('start_date', null, ['class' => 'form-control d_week', 'required' => 'required', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('Announcement End Date'), ['class' => 'col-form-label']) }}<x-required></x-required>
                {{ Form::text('end_date', null, ['class' => 'form-control d_week', 'required' => 'required', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="form-group">
            {{ Form::label('description', __('Announcement Description'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::textarea('description', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Announcement Description'), 'rows' => '3']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}
