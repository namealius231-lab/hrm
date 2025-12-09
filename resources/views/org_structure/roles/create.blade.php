@extends('layouts.admin')

@section('page-title')
    {{ __('Create Org Role') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('org-roles.index') }}">{{ __('Org Roles') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create') }}</li>
@endsection

@section('content')
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Role Blueprint') }}</h5>
            </div>
            <div class="card-body">
                {!! Form::open(['route' => 'org-roles.store', 'method' => 'POST']) !!}
                <div class="row g-3">
                    <div class="col-md-6">
                        {!! Form::label('name', __('Name'), ['class' => 'form-label']) !!}
                        {!! Form::text('name', null, ['class' => 'form-control', 'required' => true]) !!}
                    </div>
                    <div class="col-md-6">
                        {!! Form::label('code', __('Code'), ['class' => 'form-label']) !!}
                        {!! Form::text('code', null, ['class' => 'form-control', 'required' => true]) !!}
                    </div>
                    <div class="col-md-6">
                        {!! Form::label('reports_to_role_id', __('Reports To'), ['class' => 'form-label']) !!}
                        <select name="reports_to_role_id" class="form-select">
                            <option value="">{{ __('Top of hierarchy') }}</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        {!! Form::label('level', __('Level'), ['class' => 'form-label']) !!}
                        {!! Form::number('level', 1, ['class' => 'form-control', 'min' => 1]) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::label('rank_weight', __('Rank Weight'), ['class' => 'form-label']) !!}
                        {!! Form::number('rank_weight', 100, ['class' => 'form-control', 'min' => 1]) !!}
                    </div>
                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="is_executive" value="1" id="executive">
                            <label for="executive" class="form-check-label">{{ __('Is executive leadership role') }}</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        {!! Form::label('responsibilities', __('Responsibilities'), ['class' => 'form-label']) !!}
                        {!! Form::textarea('responsibilities', null, ['class' => 'form-control', 'rows' => 4]) !!}
                    </div>
                </div>
                <div class="text-end mt-4">
                    <a href="{{ route('org-roles.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary ms-2">{{ __('Save Role') }}</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection

