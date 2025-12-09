@extends('layouts.auth')
@section('page-title')
    {{ __('Register') }}
@endsection

@php
    // $logo=asset(Storage::url('uploads/logo/'));
    try {
        $logo = \App\Models\Utility::get_file('uploads/logo/');
    } catch (\Exception $e) {
        $logo = asset('uploads/logo/');
    }

    $lang = $lang ?? \App::getLocale();
    
    try {
        $LangName = \App\Models\Languages::where('code', $lang)->first();
        if (empty($LangName)) {
            $LangName = new App\Models\Utility();
            $LangName->fullName = 'English';
        }
    } catch (\Exception $e) {
        $LangName = new App\Models\Utility();
        $LangName->fullName = 'English';
    }

    try {
        $settings = \App\Models\Utility::settings();
    } catch (\Exception $e) {
        $settings = [
            'recaptcha_module' => '',
            'google_recaptcha_version' => '',
            'google_recaptcha_key' => '',
            'cust_darklayout' => ''
        ];
    }

@endphp

@section('language-bar')
    <li class="nav-item">
        <select name="language" id="language" class="lang-dropdown btn btn-primary my-1 me-2"
            onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
            {{-- @foreach (App\Models\Utility::languages() as $language)
                <option @if ($lang == $language) selected @endif value="{{ route('login', $language) }}">
                    {{ Str::upper($language) }}</option>
            @endforeach --}}
            @php
                try {
                    $languages = \App\Models\Utility::languages();
                } catch (\Exception $e) {
                    $languages = ['en' => 'English'];
                }
            @endphp
            @foreach ($languages as $code => $language)
                <option @if ($lang == $code) selected @endif value="{{ route('register', $code) }}">
                    {{ ucfirst($language) }}
                </option>
            @endforeach
        </select>
    </li>
@endsection

@if (isset($settings['cust_darklayout']) && $settings['cust_darklayout'] == 'on')
    <style>
        .g-recaptcha {
            filter: invert(1) hue-rotate(180deg) !important;
        }
    </style>
@endif

@section('content')
    <div class="card">
        <div class="row align-items-center text-start">
            <div class="col-xl-6">
                {{ Form::open(['route' => 'register', 'method' => 'post', 'id' => 'loginForm']) }}
                <div class="card-body">
                    @if (session('status'))
                        <div class="mb-4 font-medium text-lg text-green-600 text-danger">
                            {{ session('status') }}
                        </div>
                    @endif
                    <div class="">
                        <h2 class="mb-3 f-w-600">{{ __('Register') }}</h2>

                    </div>
                    <div class="">
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Full Name') }}</label>
                            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Username')]) }}
                        </div>
                        @error('name')
                            <span class="error invalid-name text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Email') }}</label>
                            {{ Form::text('email', null, ['class' => 'form-control', 'placeholder' => __('Email address')]) }}
                        </div>
                        @error('email')
                            <span class="error invalid-email text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Password') }}</label>
                            {{ Form::password('password', ['class' => 'form-control', 'id' => 'input-password', 'placeholder' => __('Password')]) }}
                        </div>
                        @error('password')
                            <span class="error invalid-password text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <div class="form-group">
                            <label class="form-label">{{ __('Confirm password') }}</label>
                            {{ Form::password('password_confirmation', ['class' => 'form-control', 'id' => 'confirm-input-password', 'placeholder' => __('Confirm Password')]) }}
                            {{-- <div class="input-group-append">
                                        <span class="input-group-text">
                                          <a href="#" data-toggle="password-text" data-target="#confirm-input-password">
                                            <i class="fas fa-eye"></i>
                                          </a>
                                        </span>
                                    </div> --}}
                            @error('password_confirmation')
                                <span class="error invalid-password_confirmation text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        @if (isset($settings['recaptcha_module']) && $settings['recaptcha_module'] == 'yes')
                            @if (isset($settings['google_recaptcha_version']) && $settings['google_recaptcha_version'] == 'v2-checkbox')
                                <div class="form-group col-lg-12 col-md-12 mt-3">
                                    {!! NoCaptcha::display() !!}
                                    @error('g-recaptcha-response')
                                        <span class="error small text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            @else
                                <div class="form-group mb-4">
                                    <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response"
                                        class="form-control">
                                    @error('g-recaptcha-response')
                                        <span class="error small text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            @endif
                        @endif
                        <div class="d-grid">
                            <button class="btn btn-primary btn-block mt-2">{{ __('Register') }}</button>
                        </div>

                    </div>
                    <p class="mb-2 my-4 text-center">{{ __('Already have an account?') }} <a href="{{ route('login') }}"
                            class="f-w-400 text-primary">{{ __('Login') }}</a></p>
                </div>
            </div>
            <div class="col-xl-6 img-card-side">
                <div class="auth-img-content">
                    <img src="../assets/images/auth/img-auth-3.svg" alt="" class="img-fluid">
                    <img src="{{ asset('assets/images/auth/img-auth-3.svg') }}" alt="" class="img-fluid">
                    <p class="text-white">
                        {{ __('The more effortless the writing looks, the more effort the writer
                                                                                                actually put into the process.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-scripts')
    @if (isset($settings['recaptcha_module']) && $settings['recaptcha_module'] == 'yes')
        @if (isset($settings['google_recaptcha_version']) && $settings['google_recaptcha_version'] == 'v2-checkbox')
            {!! NoCaptcha::renderJs() !!}
        @else
            <script src="https://www.google.com/recaptcha/api.js?render={{ $settings['google_recaptcha_key'] }}"></script>
            <script>
                $(document).ready(function() {
                    grecaptcha.ready(function() {
                        grecaptcha.execute('{{ $settings['google_recaptcha_key'] }}', {
                            action: 'submit'
                        }).then(function(token) {
                            $('#g-recaptcha-response').val(token);
                        });
                    });
                });
            </script>
        @endif
    @endif
@endpush
