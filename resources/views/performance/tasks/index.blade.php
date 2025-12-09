@php
    use Illuminate\Support\Str;
@endphp

@extends('layouts.admin')

@section('page-title')
    {{ __('Performance Tasks') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Performance Pulse') }}</li>
    <li class="breadcrumb-item">{{ __('Tasks') }}</li>
@endsection

@section('action-button')
    @can('Manage Performance Pulse')
        <div class="float-end">
            <a href="{{ route('performance-tasks.create') }}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i> {{ __('Assign Task') }}
            </a>
        </div>
    @endcan
@endsection

@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row g-3 w-100 align-items-end">
                    <div class="col-md-4">
                        <h5 class="mb-0">{{ __('Workload Overview') }}</h5>
                        <p class="text-muted mb-0">{{ __('Track every assignment and its current pulse in one grid.') }}</p>
                    </div>
                    <div class="col-md-8 text-md-end">
                        <form method="GET" class="row g-2 justify-content-md-end">
                            <div class="col-sm-6 col-md-4">
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                    placeholder="{{ __('Search title...') }}">
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="">{{ __('All Statuses') }}</option>
                                    @foreach (['pending', 'in_progress', 'completed', 'blocked', 'overdue', 'archived'] as $status)
                                        <option value="{{ $status }}" @selected(request('status') === $status)>
                                            {{ __(Str::headline($status)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Task') }}</th>
                            <th>{{ __('Timeline') }}</th>
                            <th>{{ __('Difficulty / Priority') }}</th>
                            <th>{{ __('Assignments') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tasks as $task)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-start flex-column">
                                        <span class="fw-semibold">{{ $task->title }}</span>
                                        <small class="text-muted">
                                            {{ __('By :name', ['name' => optional($task->owner)->name ?? __('System')]) }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <small class="text-muted">{{ __('Start') }}</small>
                                        <span class="fw-semibold">
                                            {{ optional($task->start_date)->format('M d, Y') ?? __('Not set') }}
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column mt-1">
                                        <small class="text-muted">{{ __('Deadline') }}</small>
                                        <span class="fw-semibold {{ $task->deadline && now()->greaterThan($task->deadline) && $task->status !== 'completed' ? 'text-danger' : '' }}">
                                            {{ optional($task->deadline)->format('M d, Y') ?? __('Not set') }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light-primary text-uppercase me-1">
                                        {{ __(Str::headline($task->difficulty)) }}
                                    </span>
                                    <span class="badge bg-light-secondary text-uppercase">
                                        {{ __(Str::headline($task->priority)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $task->assignments_count }}</span>
                                    <small class="text-muted d-block">{{ __('team mates') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'primary' : 'warning') }}">
                                        {{ __(Str::headline($task->status)) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('performance-tasks.show', $task) }}" class="btn btn-sm btn-light-primary"
                                            data-bs-toggle="tooltip" title="{{ __('View') }}">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        @can('Manage Performance Pulse')
                                            <a href="{{ route('performance-tasks.edit', $task) }}" class="btn btn-sm btn-light-secondary"
                                                data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['performance-tasks.destroy', $task], 'class' => 'd-inline']) !!}
                                            <button type="submit" class="btn btn-sm btn-light-danger"
                                                onclick="return confirm('{{ __('Are you sure?') }}')" data-bs-toggle="tooltip"
                                                title="{{ __('Delete') }}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                            {!! Form::close() !!}
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="text-center py-4 text-muted">
                                        <i class="ti ti-inbox mb-2" style="font-size: 2rem;"></i>
                                        <p class="mb-0">{{ __('No performance tasks recorded yet.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-end">
                {{ $tasks->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection

