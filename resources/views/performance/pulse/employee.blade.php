@php
    use Illuminate\Support\Str;
@endphp

@extends('layouts.admin')

@section('page-title')
    {{ __('My Performance') }}
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
                        <small class="text-muted text-uppercase">{{ __('Pending') }}</small>
                        <div class="h3 mb-0">{{ $kpis['pending'] }}</div>
                        <span class="text-muted">{{ __('Need your action') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-none border h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase">{{ __('Completed') }}</small>
                        <div class="h3 mb-0">{{ $kpis['completed'] }}</div>
                        <span class="text-muted">{{ __('Done so far') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-none border h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase">{{ __('Overdue') }}</small>
                        <div class="h3 mb-0 text-danger">{{ $kpis['overdue'] }}</div>
                        <span class="text-muted">{{ __('Needs attention') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-none border h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase">{{ __('Last Update') }}</small>
                        <div class="h5 mb-0">{{ optional($kpis['last_activity_at'])->diffForHumans() ?? __('Never') }}</div>
                        <span class="text-muted">{{ __('Timeline heartbeat') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('My Assignments') }}</h5>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Task') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Progress') }}</th>
                            <th>{{ __('Updated') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assignments as $assignment)
                            <tr>
                                <td>
                                    <strong>{{ optional($assignment->task)->title ?? __('Unknown') }}</strong>
                                    <div class="text-muted small">{{ __(Str::headline(optional($assignment->task)->difficulty ?? '')) }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ __(Str::headline($assignment->status)) }}</span>
                                </td>
                                <td>
                                    <div class="progress" style="height:6px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $assignment->progress_percent }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $assignment->progress_percent }}%</small>
                                </td>
                                <td>{{ optional($assignment->updated_at)->diffForHumans() ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('performance-tasks.show', $assignment->task) }}" class="btn btn-sm btn-light">
                                        <i class="ti ti-external-link"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <p class="text-center text-muted mb-0">{{ __('No assignments yet.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4 h-100">
            <div class="card-header">
                <h5 class="mb-0">{{ __('AI Reflection') }}</h5>
            </div>
            <div class="card-body">
                @if ($insight)
                    <p class="mb-2">{{ $insight->response }}</p>
                    <small class="text-muted">{{ __('Updated :time', ['time' => $insight->created_at->diffForHumans()]) }}</small>
                @else
                    <p class="text-muted mb-0">{{ __('Insights will appear once your manager triggers the AI analysis.') }}</p>
                @endif
                @if ($insightMetrics)
                    <div class="row g-3 mt-3">
                        <div class="col-4">
                            <small class="text-muted">{{ __('On-time') }}</small>
                            <div class="h6">{{ $insightMetrics['on_time_rate'] }}%</div>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">{{ __('Avg hrs/task') }}</small>
                            <div class="h6">{{ $insightMetrics['avg_turnaround_hours'] }}</div>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">{{ __('Total tasks') }}</small>
                            <div class="h6">{{ $insightMetrics['total_tasks'] }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">{{ __('Status Mix') }}</h6>
            </div>
            <div class="card-body">
                <canvas id="myStatusChart" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">{{ __('Efficiency Trend') }}</h6>
            </div>
            <div class="card-body">
                <canvas id="myEfficiencyChart" height="220"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const personalStatus = @json($charts['status']);
            const statusCtx = document.getElementById('myStatusChart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: personalStatus.map(item => item.status),
                        datasets: [{
                            data: personalStatus.map(item => item.count),
                            backgroundColor: ['#4dabf7', '#51cf66', '#ffa94d', '#ff6b6b', '#ced4da', '#845ef7', '#5c7cfa']
                        }]
                    },
                    options: {
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            const myEfficiency = @json($charts['efficiency']);
            const efficiencyCtx = document.getElementById('myEfficiencyChart');
            if (efficiencyCtx) {
                new Chart(efficiencyCtx, {
                    type: 'line',
                    data: {
                        labels: myEfficiency.map(item => item.month),
                        datasets: [{
                                label: '{{ __('On-time') }}',
                                data: myEfficiency.map(item => item.on_time_rate),
                                borderColor: '#51cf66',
                                tension: 0.3
                            },
                            {
                                label: '{{ __('Late') }}',
                                data: myEfficiency.map(item => item.late_rate),
                                borderColor: '#ff6b6b',
                                tension: 0.3
                            }
                        ]
                    },
                    options: {
                        plugins: {
                            legend: { position: 'bottom' }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush

