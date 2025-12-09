@php
    use Illuminate\Support\Str;
@endphp

@extends('layouts.admin')

@section('page-title')
    {{ __('Org Roles') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Org Structure') }}</li>
    <li class="breadcrumb-item">{{ __('Roles') }}</li>
@endsection

@section('action-button')
    <div class="float-end">
        <a href="{{ route('org-roles.create') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i> {{ __('Add Role') }}
        </a>
    </div>
@endsection

@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Hierarchy Map') }}</h5>
            </div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Role') }}</th>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Reports To') }}</th>
                            <th>{{ __('Level') }}</th>
                            <th>{{ __('Rank Weight') }}</th>
                            <th>{{ __('Executive') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                            <tr>
                                <td>
                                    <strong>{{ $role->name }}</strong>
                                    @if ($role->responsibilities)
                                        <p class="text-muted small mb-0">{{ Str::limit($role->responsibilities, 60) }}</p>
                                    @endif
                                </td>
                                <td>{{ $role->code }}</td>
                                <td>{{ optional($role->parent)->name ?? '—' }}</td>
                                <td>{{ $role->level }}</td>
                                <td>{{ $role->rank_weight }}</td>
                                <td>
                                    <span class="badge bg-{{ $role->is_executive ? 'success' : 'secondary' }}">
                                        {{ $role->is_executive ? __('Yes') : __('No') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('org-roles.edit', $role) }}" class="btn btn-sm btn-light">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    {!! Form::open(['method' => 'DELETE', 'route' => ['org-roles.destroy', $role], 'class' => 'd-inline']) !!}
                                    <button type="submit" class="btn btn-sm btn-light text-danger"
                                        onclick="return confirm('{{ __('Delete this role?') }}')">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <p class="text-center text-muted mb-0">{{ __('No roles yet. Start by creating one!') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

