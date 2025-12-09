@php
    use Illuminate\Support\Str;
@endphp

@extends('layouts.admin')

@section('page-title')
    {{ __('Performance Pulse') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Performance Pulse') }}</li>
@endsection

@section('content')
    <div class="col-12">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card shadow-none border h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase">{{ __('Employees') }}</small>
                        <div class="h3 mb-0">{{ number_format($kpis['total_employees']) }}</div>
                        <span class="text-muted">{{ __('Active headcount') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-none border h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase">{{ __('Active Tasks') }}</small>
                        <div class="h3 mb-0">{{ number_format($kpis['active_tasks']) }}</div>
                        <span class="text-muted">{{ __('In-flight workload') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-none border h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase">{{ __('Attendance Today') }}</small>
                        <div class="h3 mb-0">{{ $kpis['attendance_rate'] }}%</div>
                        <span class="text-muted">{{ __('Presence ratio') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-none border h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase">{{ __('Overdue Tasks') }}</small>
                        <div class="h3 mb-0">{{ number_format($kpis['overdue_tasks']) }}</div>
                        <span class="text-muted">{{ __('Needs attention') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">{{ __('Focus Employee') }}</h5>
                    <small class="text-muted">{{ __('Drill down into an individual performance lane.') }}</small>
                </div>
                <form method="GET" class="d-flex gap-2">
                    <select name="employee_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(optional($selectedEmployee)->id === $employee->id)>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-light">{{ __('Apply') }}</button>
                </form>
            </div>
            <div class="card-body">
                @if ($selectedEmployee && $employeeKpis)
                    <div class="row g-3">
                        <div class="col-md-3">
                            <small class="text-muted">{{ __('Pending') }}</small>
                            <div class="h5">{{ $employeeKpis['pending'] }}</div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">{{ __('Completed') }}</small>
                            <div class="h5">{{ $employeeKpis['completed'] }}</div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">{{ __('Overdue') }}</small>
                            <div class="h5 text-danger">{{ $employeeKpis['overdue'] }}</div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">{{ __('Last Activity') }}</small>
                            <div class="h5">{{ optional($employeeKpis['last_activity_at'])->diffForHumans() ?? '—' }}</div>
                        </div>
                    </div>
                @else
                    <p class="text-muted mb-0">{{ __('Add employees first to unlock drill-down analytics.') }}</p>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('AI Insight') }}</h5>
            </div>
            <div class="card-body">
                @if ($selectedEmployee && $employeeInsight)
                    <p class="mb-2">{{ $employeeInsight->response }}</p>
                    <small class="text-muted d-block">{{ __('Generated :time', ['time' => $employeeInsight->created_at->diffForHumans()]) }}</small>
                @elseif($selectedEmployee)
                    <p class="text-muted">{{ __('No AI insight yet for :name.', ['name' => $selectedEmployee->name]) }}</p>
                @else
                    <p class="text-muted">{{ __('Select an employee to generate AI insights.') }}</p>
                @endif
                @can('Manage Performance Pulse')
                    @if ($selectedEmployee)
                        <form method="POST" action="{{ route('performance.pulse.insight') }}" class="mt-3">
                            @csrf
                            <input type="hidden" name="employee_id" value="{{ $selectedEmployee->id }}">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="ti ti-wand"></i> {{ __('Generate Insight') }}
                            </button>
                        </form>
                    @endif
                @endcan
                @if ($employeeInsightMetrics)
                    <div class="row g-3 mt-3">
                        <div class="col-4">
                            <small class="text-muted">{{ __('On-time Rate') }}</small>
                            <div class="h6">{{ $employeeInsightMetrics['on_time_rate'] }}%</div>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">{{ __('Avg Turnaround (hrs)') }}</small>
                            <div class="h6">{{ $employeeInsightMetrics['avg_turnaround_hours'] }}</div>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">{{ __('Pending') }}</small>
                            <div class="h6">{{ $employeeInsightMetrics['pending_tasks'] }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4 h-100">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Communication Hub') }}</h5>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="commTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="direct-tab" data-bs-toggle="tab" data-bs-target="#direct"
                            type="button" role="tab">{{ __('Direct') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="announcement-tab" data-bs-toggle="tab" data-bs-target="#announcement"
                            type="button" role="tab">{{ __('Announcement') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="meeting-tab" data-bs-toggle="tab" data-bs-target="#meeting"
                            type="button" role="tab">{{ __('Meeting') }}</button>
                    </li>
                </ul>
                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="direct" role="tabpanel">
                        {!! Form::open(['route' => 'performance.communication.direct', 'method' => 'POST']) !!}
                        <div class="mb-3">
                            {!! Form::label('employee_id', __('Recipient'), ['class' => 'form-label']) !!}
                            <select name="employee_id" class="form-select" required>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            {!! Form::label('message', __('Message'), ['class' => 'form-label']) !!}
                            {!! Form::textarea('message', null, ['class' => 'form-control', 'rows' => 3, 'required' => true]) !!}
                        </div>
                        <button type="submit" class="btn btn-primary w-100">{{ __('Send Message') }}</button>
                        {!! Form::close() !!}
                    </div>
                    <div class="tab-pane fade" id="announcement" role="tabpanel">
                        {!! Form::open(['route' => 'performance.communication.announcement', 'method' => 'POST']) !!}
                        <div class="mb-2">
                            {!! Form::label('title', __('Title'), ['class' => 'form-label']) !!}
                            {!! Form::text('title', null, ['class' => 'form-control', 'required' => true]) !!}
                        </div>
                        <div class="mb-2">
                            {!! Form::label('message', __('Message'), ['class' => 'form-label']) !!}
                            {!! Form::textarea('message', null, ['class' => 'form-control', 'rows' => 3, 'required' => true]) !!}
                        </div>
                        <div class="mb-2">
                            {!! Form::label('audience', __('Audience'), ['class' => 'form-label']) !!}
                            {!! Form::select('audience', ['all' => __('Entire company'), 'department' => __('Selected departments'), 'custom' => __('Specific employees')], 'all', ['class' => 'form-select']) !!}
                        </div>
                        <div class="mb-2">
                            {!! Form::label('department_ids', __('Departments'), ['class' => 'form-label']) !!}
                            <select name="department_ids[]" class="form-select" multiple>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            {!! Form::label('employee_ids', __('Employees'), ['class' => 'form-label']) !!}
                            <select name="employee_ids[]" class="form-select" multiple>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">{{ __('Publish Announcement') }}</button>
                        {!! Form::close() !!}
                    </div>
                    <div class="tab-pane fade" id="meeting" role="tabpanel">
                        {!! Form::open(['route' => 'performance.communication.meeting', 'method' => 'POST']) !!}
                        <div class="mb-2">
                            {!! Form::label('title', __('Title'), ['class' => 'form-label']) !!}
                            {!! Form::text('title', null, ['class' => 'form-control', 'required' => true]) !!}
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                {!! Form::label('date', __('Date'), ['class' => 'form-label']) !!}
                                <input type="date" name="date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                {!! Form::label('time', __('Time'), ['class' => 'form-label']) !!}
                                <input type="time" name="time" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-2 mt-2">
                            {!! Form::label('meeting_link', __('Video Link'), ['class' => 'form-label']) !!}
                            {!! Form::text('meeting_link', null, ['class' => 'form-control']) !!}
                        </div>
                        <div class="mb-2">
                            {!! Form::label('audience', __('Audience'), ['class' => 'form-label']) !!}
                            {!! Form::select('audience', ['all' => __('Entire company'), 'department' => __('Selected departments'), 'custom' => __('Specific employees')], 'all', ['class' => 'form-select']) !!}
                        </div>
                        <div class="mb-2">
                            {!! Form::label('department_ids', __('Departments'), ['class' => 'form-label']) !!}
                            <select name="department_ids[]" class="form-select" multiple>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            {!! Form::label('employee_ids', __('Employees'), ['class' => 'form-label']) !!}
                            <select name="employee_ids[]" class="form-select" multiple>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">{{ __('Schedule Meeting') }}</button>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('Workload Snapshot') }}</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('Timeline Variance') }}</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="timelineChart" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('Productivity Trend') }}</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="productivityChart" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('Efficiency Trend') }}</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="efficiencyChart" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('Burnout Matrix') }}</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="burnoutChart" height="240"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusData = @json($statusDistribution);
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: statusData.map(item => item.status),
                        datasets: [{
                            data: statusData.map(item => item.count),
                            backgroundColor: ['#4c6ef5', '#9775fa', '#40c057', '#fcc419', '#ff6b6b', '#ced4da', '#adb5bd'],
                        }]
                    },
                    options: {
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }

            const timelineData = @json($timelineVariance);
            const timelineCtx = document.getElementById('timelineChart');
            if (timelineCtx) {
                new Chart(timelineCtx, {
                    type: 'bar',
                    data: {
                        labels: timelineData.map(item => item.task),
                        datasets: [{
                                label: '{{ __('Planned') }}',
                                data: timelineData.map(item => item.planned_hours),
                                backgroundColor: '#74c0fc'
                            },
                            {
                                label: '{{ __('Actual') }}',
                                data: timelineData.map(item => item.actual_hours),
                                backgroundColor: '#ff922b'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'bottom' } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            const productivityData = @json($productivityTrend);
            const productivityCtx = document.getElementById('productivityChart');
            if (productivityCtx) {
                new Chart(productivityCtx, {
                    type: 'line',
                    data: {
                        labels: productivityData.map(item => item.label),
                        datasets: [{
                            label: '{{ __('Completed Tasks') }}',
                            data: productivityData.map(item => item.completed),
                            borderColor: '#4c6ef5',
                            backgroundColor: 'rgba(76,110,245,0.1)',
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            const efficiencyData = @json($efficiencyTrend);
            const efficiencyCtx = document.getElementById('efficiencyChart');
            if (efficiencyCtx) {
                new Chart(efficiencyCtx, {
                    type: 'line',
                    data: {
                        labels: efficiencyData.map(item => item.month),
                        datasets: [{
                                label: '{{ __('On-time %') }}',
                                data: efficiencyData.map(item => item.on_time_rate),
                                borderColor: '#40c057',
                                tension: 0.3
                            },
                            {
                                label: '{{ __('Late %') }}',
                                data: efficiencyData.map(item => item.late_rate),
                                borderColor: '#ff6b6b',
                                tension: 0.3
                            }
                        ]
                    },
                    options: {
                        plugins: { legend: { position: 'bottom' } },
                        scales: { y: { beginAtZero: true, max: 100 } }
                    }
                });
            }

            const burnoutData = @json($burnoutMatrix);
            const burnoutCtx = document.getElementById('burnoutChart');
            if (burnoutCtx) {
                new Chart(burnoutCtx, {
                    type: 'scatter',
                    data: {
                        datasets: burnoutData.map(item => ({
                            label: item.employee_name,
                            data: [{
                                x: item.workload,
                                y: item.avg_days
                            }],
                            backgroundColor: item.avg_difficulty >= 2.5 ? '#ff6b6b' : '#4c6ef5',
                            radius: 6 + (item.avg_difficulty * 2)
                        }))
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        scales: {
                            x: {
                                title: { display: true, text: '{{ __('Workload') }}' }
                            },
                            y: {
                                title: { display: true, text: '{{ __('Avg days to finish') }}' }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush

