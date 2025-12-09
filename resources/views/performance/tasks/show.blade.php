@php
    use Illuminate\Support\Str;
@endphp

@extends('layouts.admin')

@section('page-title')
    {{ __('Task • :title', ['title' => $task->title]) }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('performance-tasks.index') }}">{{ __('Tasks') }}</a></li>
    <li class="breadcrumb-item">{{ __('Details') }}</li>
@endsection

@section('action-button')
    <div class="float-end">
        <a href="{{ route('performance-tasks.index') }}" class="btn btn-sm btn-light">
            <i class="ti ti-arrow-left"></i> {{ __('Back to list') }}
        </a>
        @can('Manage Performance Pulse')
            <a href="{{ route('performance-tasks.edit', $task) }}" class="btn btn-sm btn-primary">
                <i class="ti ti-edit"></i> {{ __('Edit') }}
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">{{ $task->title }}</h4>
                        <p class="text-muted mb-0">{{ __('Owned by :name', ['name' => optional($task->owner)->name ?? __('System')]) }}</p>
                    </div>
                    <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'primary' : 'warning') }} text-uppercase">
                        {{ __(Str::headline($task->status)) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-sm-4">
                        <small class="text-muted">{{ __('Difficulty') }}</small>
                        <div class="fw-semibold">{{ __(Str::headline($task->difficulty)) }}</div>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted">{{ __('Priority') }}</small>
                        <div class="fw-semibold">{{ __(Str::headline($task->priority)) }}</div>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted">{{ __('Expected Hours') }}</small>
                        <div class="fw-semibold">{{ $task->expected_hours ?: '—' }}</div>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted">{{ __('Start Date') }}</small>
                        <div class="fw-semibold">{{ optional($task->start_date)->format('M d, Y') ?: __('TBD') }}</div>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted">{{ __('Deadline') }}</small>
                        <div class="fw-semibold {{ $task->deadline && now()->greaterThan($task->deadline) && $task->status !== 'completed' ? 'text-danger' : '' }}">
                            {{ optional($task->deadline)->format('M d, Y') ?: __('TBD') }}
                        </div>
                    </div>
                </div>
                <h6 class="text-uppercase text-muted mb-2">{{ __('Brief') }}</h6>
                <p class="mb-0">{{ $task->description ?? __('No description provided.') }}</p>
            </div>
        </div>

        @if ($task->ai_summary)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('AI Snapshot') }}</h5>
                    <small class="text-muted">{{ __('Generated') }} {{ optional($task->ai_summary_generated_at)->diffForHumans() }}</small>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $task->ai_summary }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Assignment Summary') }}</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <small class="text-muted">{{ __('Total Assignees') }}</small>
                        <div class="h4 mb-0">{{ $task->assignments->count() }}</div>
                    </div>
                    <div>
                        <small class="text-muted">{{ __('Active') }}</small>
                        <div class="h4 mb-0">{{ $task->activeAssignments()->count() }}</div>
                    </div>
                </div>
                <div class="progress mb-2">
                    @php
                        $completed = $task->assignments->where('status', 'completed')->count();
                        $total = max(1, $task->assignments->count());
                        $percent = round(($completed / $total) * 100, 1);
                    @endphp
                    <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted">{{ __('Overall completion :percent%', ['percent' => $percent]) }}</small>
            </div>
        </div>
    </div>

    <div class="col-12">
        <h5 class="mb-3">{{ __('Assignments & Updates') }}</h5>

        @foreach ($task->assignments as $assignment)
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0">{{ optional($assignment->employee)->name ?? __('Unknown') }}</h6>
                        <small class="text-muted">{{ __('Progress :percent%', ['percent' => $assignment->progress_percent]) }}</small>
                    </div>
                    <span class="badge bg-light text-dark text-uppercase">
                        {{ __(Str::headline($assignment->status)) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-3">
                            <small class="text-muted">{{ __('Started') }}</small>
                            <div class="fw-semibold">{{ optional($assignment->started_at)->format('M d, Y H:i') ?? '—' }}</div>
                        </div>
                        <div class="col-sm-3">
                            <small class="text-muted">{{ __('Completed') }}</small>
                            <div class="fw-semibold">{{ optional($assignment->completed_at)->format('M d, Y H:i') ?? '—' }}</div>
                        </div>
                        <div class="col-sm-3">
                            <small class="text-muted">{{ __('Turnaround (hrs)') }}</small>
                            <div class="fw-semibold">
                                {{ $assignment->turnaround_minutes ? round($assignment->turnaround_minutes / 60, 1) : '—' }}
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <small class="text-muted">{{ __('Last Update') }}</small>
                            <div class="fw-semibold">{{ optional($assignment->last_progress_at)->diffForHumans() ?? __('Never') }}</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase small">{{ __('Recent Updates') }}</h6>
                        @forelse ($assignment->updates as $update)
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ __(Str::headline($update->status)) }}</strong>
                                    <small class="text-muted">{{ optional($update->created_at)->diffForHumans() }}</small>
                                </div>
                                <p class="mb-1">{{ $update->summary ?? __('No summary provided.') }}</p>
                                @if ($update->strategy)
                                    <small class="d-block"><span class="text-muted">{{ __('Strategy:') }}</span> {{ $update->strategy }}</small>
                                @endif
                                @if ($update->blockers)
                                    <small class="d-block text-danger"><span class="text-muted">{{ __('Blockers:') }}</span>
                                        {{ $update->blockers }}</small>
                                @endif
                                @if ($update->files->isNotEmpty())
                                    <div class="mt-2">
                                        <small class="text-muted d-block">{{ __('Evidence') }}</small>
                                        @foreach ($update->files as $file)
                                            <a href="{{ $file->file_path }}" target="_blank" class="badge bg-light text-dark me-1">
                                                <i class="ti ti-paperclip"></i> {{ Str::limit($file->file_name, 20) }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted">{{ __('No updates yet.') }}</p>
                        @endforelse
                    </div>

                    @if (Auth::user()->can('Manage Performance Pulse') || (Auth::user()->employee && Auth::user()->employee->id === $assignment->employee_id))
                        <div class="border rounded p-3 mb-4">
                            <h6 class="mb-3">{{ __('Log Progress') }}</h6>
                            {!! Form::open(['route' => ['performance-assignments.updates.store', $assignment], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
                            <div class="row g-3">
                                <div class="col-md-4">
                                    {!! Form::label('status', __('Status'), ['class' => 'form-label']) !!}
                                    {!! Form::select('status', collect(['pending', 'in_progress', 'blocked', 'completed'])->mapWithKeys(fn($value) => [$value => __(Str::headline($value))]), $assignment->status, ['class' => 'form-select', 'required' => true]) !!}
                                </div>
                                <div class="col-md-4">
                                    {!! Form::label('progress_percent', __('Progress %'), ['class' => 'form-label']) !!}
                                    {!! Form::number('progress_percent', $assignment->progress_percent, ['class' => 'form-control', 'min' => 0, 'max' => 100, 'required' => true]) !!}
                                </div>
                                <div class="col-md-4">
                                    {!! Form::label('evidence', __('Evidence'), ['class' => 'form-label']) !!}
                                    <input type="file" name="evidence[]" class="form-control" multiple>
                                </div>
                                <div class="col-md-12">
                                    {!! Form::label('summary', __('Summary'), ['class' => 'form-label']) !!}
                                    {!! Form::textarea('summary', null, ['class' => 'form-control', 'rows' => 2]) !!}
                                </div>
                                <div class="col-md-6">
                                    {!! Form::label('strategy', __('Strategy'), ['class' => 'form-label']) !!}
                                    {!! Form::textarea('strategy', null, ['class' => 'form-control', 'rows' => 2]) !!}
                                </div>
                                <div class="col-md-6">
                                    {!! Form::label('blockers', __('Blockers'), ['class' => 'form-label']) !!}
                                    {!! Form::textarea('blockers', null, ['class' => 'form-control', 'rows' => 2]) !!}
                                </div>
                            </div>
                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="ti ti-send"></i> {{ __('Update Progress') }}
                                </button>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    @endif

                    @can('Manage Performance Pulse')
                        <div class="border rounded p-3">
                            <h6 class="mb-3">{{ __('Manager Review') }}</h6>
                            {!! Form::open(['route' => ['performance-assignments.reviews.store', $assignment], 'method' => 'POST']) !!}
                            <div class="row g-3">
                                <div class="col-md-4">
                                    {!! Form::label('rating', __('Rating (1-5)'), ['class' => 'form-label']) !!}
                                    {!! Form::number('rating', 5, ['class' => 'form-control', 'min' => 1, 'max' => 5, 'required' => true]) !!}
                                </div>
                                <div class="col-md-4">
                                    {!! Form::label('efficiency_score', __('Efficiency %'), ['class' => 'form-label']) !!}
                                    {!! Form::number('efficiency_score', null, ['class' => 'form-control', 'min' => 0, 'max' => 100]) !!}
                                </div>
                                <div class="col-md-4">
                                    {!! Form::label('quality_score', __('Quality %'), ['class' => 'form-label']) !!}
                                    {!! Form::number('quality_score', null, ['class' => 'form-control', 'min' => 0, 'max' => 100]) !!}
                                </div>
                                <div class="col-md-6">
                                    {!! Form::label('review_period_start', __('Review Period From'), ['class' => 'form-label']) !!}
                                    <input type="date" name="review_period_start" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    {!! Form::label('review_period_end', __('Review Period To'), ['class' => 'form-label']) !!}
                                    <input type="date" name="review_period_end" class="form-control">
                                </div>
                                <div class="col-md-12">
                                    {!! Form::label('summary', __('Summary'), ['class' => 'form-label']) !!}
                                    {!! Form::textarea('summary', null, ['class' => 'form-control', 'rows' => 2]) !!}
                                </div>
                                <div class="col-md-6">
                                    {!! Form::label('strengths', __('Strengths'), ['class' => 'form-label']) !!}
                                    {!! Form::textarea('strengths', null, ['class' => 'form-control', 'rows' => 2]) !!}
                                </div>
                                <div class="col-md-6">
                                    {!! Form::label('improvements', __('Improvements'), ['class' => 'form-label']) !!}
                                    {!! Form::textarea('improvements', null, ['class' => 'form-control', 'rows' => 2]) !!}
                                </div>
                            </div>
                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="ti ti-star"></i> {{ __('Save Review') }}
                                </button>
                            </div>
                            {!! Form::close() !!}

                            @if ($assignment->reviews->isNotEmpty())
                                <div class="mt-4">
                                    <h6 class="text-muted text-uppercase small">{{ __('Past Reviews') }}</h6>
                                    @foreach ($assignment->reviews as $review)
                                        <div class="border rounded p-3 mb-3">
                                            <div class="d-flex justify-content-between">
                                                <strong>{{ __('Rating: :score/5', ['score' => $review->rating]) }}</strong>
                                                <small class="text-muted">{{ optional($review->created_at)->format('M d, Y') }}</small>
                                            </div>
                                            <p class="mb-1">{{ $review->summary }}</p>
                                            <small class="d-block text-success">{{ __('Strengths: :text', ['text' => $review->strengths ?? __('n/a')]) }}</small>
                                            <small class="d-block text-warning">{{ __('Improvements: :text', ['text' => $review->improvements ?? __('n/a')]) }}</small>
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['performance-reviews.destroy', $review], 'class' => 'text-end mt-2']) !!}
                                            <button type="submit" class="btn btn-sm btn-link text-danger" onclick="return confirm('{{ __('Remove review?') }}')">
                                                {{ __('Delete') }}
                                            </button>
                                            {!! Form::close() !!}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endcan
                </div>
            </div>
        @endforeach
    </div>
@endsection

