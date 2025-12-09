@php
    use Illuminate\Support\Str;
@endphp

@extends('layouts.admin')

@section('page-title')
    {{ __('Update Performance Task') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('performance-tasks.index') }}">{{ __('Performance Tasks') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit') }}</li>
@endsection

@section('content')
    <div class="col-xl-9">
        <div class="card">
            <div class="card-header">
                <h5>{{ $performanceTask->title }}</h5>
                <p class="text-muted mb-0">{{ __('Tune the expectation, update the crew, or adjust the timeline.') }}</p>
            </div>
            <div class="card-body">
                {!! Form::model($performanceTask, ['route' => ['performance-tasks.update', $performanceTask], 'method' => 'PUT']) !!}
                <div class="row g-3">
                    <div class="col-md-8">
                        {!! Form::label('title', __('Title'), ['class' => 'form-label']) !!}
                        {!! Form::text('title', null, ['class' => 'form-control', 'required' => true]) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::label('status', __('Status'), ['class' => 'form-label']) !!}
                        {!! Form::select('status', collect(['pending', 'in_progress', 'completed', 'blocked', 'overdue', 'archived'])->mapWithKeys(fn($value) => [$value => __(Str::headline($value))]), null, ['class' => 'form-select', 'required' => true]) !!}
                    </div>
                    <div class="col-md-12">
                        {!! Form::label('description', __('Detailed Brief'), ['class' => 'form-label']) !!}
                        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 4]) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::label('difficulty', __('Difficulty'), ['class' => 'form-label']) !!}
                        {!! Form::select('difficulty', collect(['low', 'medium', 'high'])->mapWithKeys(fn($value) => [$value => __(Str::headline($value))]), null, ['class' => 'form-select', 'required' => true]) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::label('priority', __('Priority'), ['class' => 'form-label']) !!}
                        {!! Form::select('priority', collect(['low', 'normal', 'high', 'critical'])->mapWithKeys(fn($value) => [$value => __(Str::headline($value))]), null, ['class' => 'form-select', 'required' => true]) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::label('expected_hours', __('Estimated Effort (hours)'), ['class' => 'form-label']) !!}
                        {!! Form::number('expected_hours', null, ['class' => 'form-control', 'min' => 0]) !!}
                    </div>
                    <div class="col-md-6">
                        {!! Form::label('start_date', __('Start Date'), ['class' => 'form-label']) !!}
                        <input type="text" name="start_date" class="form-control datepicker" value="{{ optional($performanceTask->start_date)->format('Y-m-d') }}" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        {!! Form::label('deadline', __('Deadline'), ['class' => 'form-label']) !!}
                        <input type="text" name="deadline" class="form-control datepicker" value="{{ optional($performanceTask->deadline)->format('Y-m-d') }}" autocomplete="off">
                    </div>
                    <div class="col-md-12">
                        {!! Form::label('employee_ids', __('Assigned Employees'), ['class' => 'form-label']) !!}
                        <select name="employee_ids[]" class="form-select" data-trigger multiple required>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" @selected(in_array($employee->id, $assigned, true))>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ __('Removing a teammate here also removes their assignment history from this task.') }}</small>
                    </div>
                </div>
                <div class="text-end mt-4">
                    <a href="{{ route('performance-tasks.show', $performanceTask) }}" class="btn btn-light">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary ms-2">
                        <i class="ti ti-device-floppy"></i> {{ __('Save Changes') }}
                    </button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        flatpickr('.datepicker', {
            dateFormat: 'Y-m-d',
        });
    </script>
@endpush

