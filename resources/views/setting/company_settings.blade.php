@extends('layouts.admin')
@section('page-title')
    {{ __('Settings') }}
@endsection
@php
    // $logo = asset(Storage::url('uploads/logo/'));
    $logo = \App\Models\Utility::get_file('uploads/logo/');
    $meta_image = \App\Models\Utility::get_file('uploads/meta/');

    $company_logo = App\Models\Utility::getValByName('company_logo');
    $company_logo_light = App\Models\Utility::getValByName('company_logo_light');
    $company_favicon = App\Models\Utility::getValByName('company_favicon');
    // $SITE_RTL = env('SITE_RTL');

    $SITE_RTL = isset($settings['SITE_RTL']) ? $settings['SITE_RTL'] : 'off';
    if ($SITE_RTL == '') {
        $SITE_RTL = 'off';
    }

    $lang = \App\Models\Utility::getValByName('default_language');
    $color = isset($settings['theme_color']) ? $settings['theme_color'] : 'theme-3';
    $is_sidebar_transperent = isset($settings['is_sidebar_transperent']) ? $settings['is_sidebar_transperent'] : 'on';
    $dark_mode = isset($settings['dark_mode']) ? $settings['dark_mode'] : '';
    $currantLang = App\Models\Utility::languages();

    $file_type = config('files_types');
    $setting = App\Models\Utility::settings();

    $local_storage_validation = isset($setting['local_storage_validation']) ? $setting['local_storage_validation'] : '';
    $local_storage_validations = !empty($local_storage_validation) ? explode(',', $local_storage_validation) : [];

    $s3_storage_validation = isset($setting['s3_storage_validation']) ? $setting['s3_storage_validation'] : '';
    $s3_storage_validations = !empty($s3_storage_validation) ? explode(',', $s3_storage_validation) : [];

    $wasabi_storage_validation = isset($setting['wasabi_storage_validation']) ? $setting['wasabi_storage_validation'] : '';
    $wasabi_storage_validations = !empty($wasabi_storage_validation) ? explode(',', $wasabi_storage_validation) : [];

    $chatgpt = App\Models\Utility::getValByName('enable_chatgpt');

    $google_recaptcha_version = ['v2-checkbox' => __('v2'), 'v3' => __('v3')];
@endphp

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Settings') }}</li>
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
@endpush

@push('script-page')
    <script src="{{ asset('css/summernote/summernote-bs4.js') }}"></script>
    <script>
        $('.colorPicker').on('click', function(e) {
            $('body').removeClass('custom-color');
            if (/^theme-\d+$/) {
                $('body').removeClassRegex(/^theme-\d+$/);
            }
            $('body').addClass('custom-color');
            $('.themes-color-change').removeClass('active_color');
            $(this).addClass('active_color');
            const input = document.getElementById("color-picker");
            setColor();
            input.addEventListener("input", setColor);

            function setColor() {
                $(':root').css('--color-customColor', input.value);
            }

            $(`input[name='color_flag`).val('true');
        });

        $('.themes-color-change').on('click', function() {

            $(`input[name='color_flag`).val('false');

            var color_val = $(this).data('value');
            $('body').removeClass('custom-color');
            if (/^theme-\d+$/) {
                $('body').removeClassRegex(/^theme-\d+$/);
            }
            $('body').addClass(color_val);
            $('.theme-color').prop('checked', false);
            $('.themes-color-change').removeClass('active_color');
            $('.colorPicker').removeClass('active_color');
            $(this).addClass('active_color');
            $(`input[value=${color_val}]`).prop('checked', true);
        });

        $.fn.removeClassRegex = function(regex) {
            return $(this).removeClass(function(index, classes) {
                return classes.split(/\s+/).filter(function(c) {
                    return regex.test(c);
                }).join(' ');
            });
        };
    </script>
    <script>
        $(document).on('change', '.email-template-checkbox', function() {
            var url = $(this).data('url');
            var chbox = $(this);

            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    status: chbox.val()
                },
                success: function(data) {

                },
            });
        });

        function enablecookie() {
            const element = $('#enable_cookie').is(':checked');
            $('.cookieDiv').addClass('disabledCookie');
            if (element == true) {
                $('.cookieDiv').removeClass('disabledCookie');
                $("#cookie_logging").attr('checked', true);
            } else {
                $('.cookieDiv').addClass('disabledCookie');
                $("#cookie_logging").attr('checked', false);
            }
        }


        // $('.themes-color-change').on('click', function() {
        //     var color_val = $(this).data('value');
        //     $('.theme-color').prop('checked', false);
        //     $('.themes-color-change').removeClass('active_color');
        //     $(this).addClass('active_color');
        //     $(`input[value=${color_val}]`).prop('checked', true);

        // });
    </script>
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300
        })
    </script>
    <script>
        document.getElementById('company_logo').onchange = function() {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('image').src = src
        }
    </script>
    <script>
        document.getElementById('company_logo_light').onchange = function() {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('image1').src = src
        }
    </script>
    <script>
        document.getElementById('company_favicon').onchange = function() {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('image2').src = src
        }
    </script>
    <script>
        $(document).on("click", '.send_email', function(e) {
            e.preventDefault();
            var title = $(this).attr('data-title');

            var size = 'md';
            var url = $(this).attr('data-url');
            if (typeof url != 'undefined') {
                $("#commonModal .modal-title").html(title);
                $("#commonModal .modal-dialog").addClass('modal-' + size);
                $("#commonModal").modal('show');
                $.post(url, {
                    _token: '{{ csrf_token() }}',
                    mail_driver: $("#mail_driver").val(),
                    mail_host: $("#mail_host").val(),
                    mail_port: $("#mail_port").val(),
                    mail_username: $("#mail_username").val(),
                    mail_password: $("#mail_password").val(),
                    mail_encryption: $("#mail_encryption").val(),
                    mail_from_address: $("#mail_from_address").val(),
                    mail_from_name: $("#mail_from_name").val(),
                }, function(data) {
                    $('#commonModal .body').html(data);
                });
            }
        });


        $(document).on('submit', '#test_email', function(e) {
            e.preventDefault();
            $("#email_sending").show();
            var post = $(this).serialize();
            var url = $(this).attr('action');
            $.ajax({
                type: "post",
                url: url,
                data: post,
                cache: false,
                beforeSend: function() {
                    $('#test_email .btn-create').attr('disabled', 'disabled');
                },
                success: function(data) {
                    if (data.is_success) {
                        show_toastr('Success', data.message, 'success');
                    } else {
                        show_toastr('Error', data.message, 'error');
                    }
                    $("#email_sending").hide();
                },
                complete: function() {
                    $('#test_email .btn-create').removeAttr('disabled');
                },
            });
        });
    </script>
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300,
        })
        $(".list-group-item").click(function() {
            $('.list-group-item').filter(function() {
                return this.href == id;
            }).parent().removeClass('text-primary');
        });

        function check_theme(color_val) {
            $('#theme_color').prop('checked', false);
            $('input[value="' + color_val + '"]').prop('checked', true);
        }

        $(document).on('change', '[name=storage_setting]', function() {
            if ($(this).val() == 's3') {
                $('.s3-setting').removeClass('d-none');
                $('.wasabi-setting').addClass('d-none');
                $('.local-setting').addClass('d-none');
            } else if ($(this).val() == 'wasabi') {
                $('.s3-setting').addClass('d-none');
                $('.wasabi-setting').removeClass('d-none');
                $('.local-setting').addClass('d-none');
            } else {
                $('.s3-setting').addClass('d-none');
                $('.wasabi-setting').addClass('d-none');
                $('.local-setting').removeClass('d-none');
            }
        });
    </script>
@endpush
@section('content')
    <div class="col-sm-12">
        <div class="row">
            <div class="col-xl-3">
                <div class="card sticky-top">
                    <div class="list-group list-group-flush" id="useradd-sidenav">

                        <a href="#brand-settings" id="brand-setting-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Brand Settings') }} <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div></a>


                        <a href="#company-settings" id="company-setting-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Company Settings') }} <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                        <a href="#system-settings" id="system-setting-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('System Settings') }} <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                        <a href="#email-settings" id="email-setting-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Email Settings') }} <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div></a>



                        <a href="#pusher-settings" id="pusher-setting-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Pusher Settings') }} <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div></a>


                        <a id="email-notification-tab" data-toggle="tab" href="#email-notification-settings" role="tab"
                            aria-controls="" aria-selected="false"
                            class="list-group-item list-group-item-action border-0">{{ __('Email Notification Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        <a href="#ip-restriction-settings" id="ip-restrict-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('IP Restriction Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        @if (Auth::user()->type == 'company')
                            <a href="#zoom-meeting-settings" id="zoom-meeting-tab"
                                class="list-group-item list-group-item-action border-0">{{ __('Zoom Meeting Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>

                            <a href="#slack-settings" id="slack-tab"
                                class="list-group-item list-group-item-action border-0">{{ __('Slack Settings') }} <div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                            <a href="#telegram-settings" id="telegram-tab"
                                class="list-group-item list-group-item-action border-0">{{ __('Telegram Settings') }} <div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                            <a href="#twilio-settings" id="twilio-tab"
                                class="list-group-item list-group-item-action border-0">{{ __('Twilio Settings') }} <div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                        @endif
                        <a href="#recaptcha-print-settings" id="recaptcha-print-setting-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Recaptcha Settings') }} <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                        <a href="#offer-letter-settings" id="offer-letter-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Offer Letter Settings') }} <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                        <a href="#joining-letter-settings" id="joining-letter-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Joining Letter Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        <a href="#experience-certificate-settings" id="experience-certificate-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Certificate of Experience Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>
<!-- 
                        <a href="#noc-settings" id="noc-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('No Objection Certificate Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        <a href="#storage-settings" id="storage-setting-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Storage Settings') }} <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>
                        <a href="#google-calender" id="google-calendar-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Google Calendar Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>
                        <a href="#SEO-settings" id="google-calendar-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('SEO Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>
                        <a href="#cache-settings" id="cache-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Cache Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>
                        <a href="#webhook-settings" id="webhook-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Webhook Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>
                        <a href="#cookie-settings" id="cache-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Cookie Consent Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>
                        <a href="#pills-chatgpt-settings" id="chatgpt-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Chat GPT Key Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        {{-- remove biometric code --}}
                        {{-- <a href="#biometric-attendance" id="biometric-attendance-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Biometric Attendance Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a> --}} -->
                    </div>

                </div>
            </div>

            <div class="col-xl-9">
                <div class="" id="brand-settings">
                    {{ Form::model($settings, ['route' => 'business.setting', 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
                    <div class="row">
                        <div class="col-lg-12 col-sm-12 col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ __('Brand Settings') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-4 col-sm-6 col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>{{ __('Logo dark') }}</h5>
                                                </div>
                                                <div class="card-body pt-0">
                                                    <div class=" setting-card">
                                                        <div class="logo-content mt-4">
                                                            <a href="{{ $logo . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'dark_logo.png') }}"
                                                                target="_blank">
                                                                <img id="image" alt="your image"
                                                                    src="{{ $logo . (isset($company_logo) && !empty($company_logo) ? $company_logo . '?' . time() : 'dark_logo.png' . '?' . time()) }}"
                                                                    width="150px" height="50px" class="big-logo">
                                                            </a>
                                                        </div>

                                                        <div class="choose-files mt-5">
                                                            <label for="company_logo">
                                                                <div class=" bg-primary company_logo"> <i
                                                                        class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                                </div>
                                                                <input type="file" class="form-control file d-none"
                                                                    name="company_logo" id="company_logo"
                                                                    data-filename="company_logo" accept=".jpeg,.jpg,.png"
                                                                    accept=".jpeg,.jpg,.png">
                                                            </label>
                                                        </div>

                                                        @error('company_logo')
                                                            <div class="row">
                                                                <span class="invalid-company_logo" role="alert">
                                                                    <strong class="text-danger">{{ $message }}</strong>
                                                                </span>
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>{{ __('Logo Light') }}</h5>
                                                </div>
                                                <div class="card-body pt-0">
                                                    <div class=" setting-card">
                                                        <div class="logo-content mt-4 ">

                                                            <a href="{{ $logo . (isset($company_logo_light) && !empty($company_logo_light) ? $company_logo_light : 'light_logo.png') }}"
                                                                target="_blank">
                                                                <img id="image1" alt="your image"
                                                                    src="{{ $logo . (isset($company_logo_light) && !empty($company_logo_light) ? $company_logo_light . '?' . time() : 'light_logo.png' . '?' . time()) }}"
                                                                    width="150px" height="50px"
                                                                    class="big-logo"style="filter: drop-shadow(2px 3px 7px #011c4b);">
                                                            </a>
                                                        </div>
                                                        <div class="choose-files mt-5">
                                                            <label for="company_logo_light">
                                                                <div class=" bg-primary company_logo_light"> <i
                                                                        class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                                </div>
                                                                <input type="file" class="form-control file d-none"
                                                                    name="company_logo_light" id="company_logo_light"
                                                                    data-filename="company_logo_light">
                                                            </label>
                                                        </div>

                                                        @error('company_logo_light')
                                                            <div class="row">
                                                                <span class="invalid-company_logo_light" role="alert">
                                                                    <strong class="text-danger">{{ $message }}</strong>
                                                                </span>
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>{{ __('Favicon') }}</h5>
                                                </div>
                                                <div class="card-body pt-0">
                                                    <div class=" setting-card">
                                                        <div class="logo-content mt-4 setting-logo ">
                                                            {{-- <img src="{{ $logo . '/' . (isset($company_favicon) && !empty($company_favicon) ? $company_favicon : 'favicon.png') }}"
                                                                width="50px" class="logo logo-sm img_setting"> --}}
                                                            <a href="{{ $logo . (isset($company_favicon) && !empty($company_favicon) ? $company_favicon : 'favicon.png') }}"
                                                                target="_blank">
                                                                <img id="image2" alt="your image"
                                                                    src="{{ $logo . (isset($company_favicon) && !empty($company_favicon) ? $company_favicon . '?' . time() : 'favicon.png' . '?' . time()) }}"
                                                                    width="50px" height="50px"
                                                                    class="big-logo"style="filter: drop-shadow(2px 3px 7px #011c4b);">
                                                            </a>
                                                        </div>

                                                        <div class="choose-files mt-5">
                                                            <label for="company_favicon">
                                                                <div class=" bg-primary company_favicon"> <i
                                                                        class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                                </div>
                                                                <input type="file" class="form-control file d-none"
                                                                    name="company_favicon" id="company_favicon"
                                                                    data-filename="company_favicon">
                                                            </label>
                                                        </div>


                                                        @error('company_favicon')
                                                            <div class="row">
                                                                <span class="invalid-logo" role="alert">
                                                                    <strong class="text-danger">{{ $message }}</strong>
                                                                </span>
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group col-md-6">

                                            {{ Form::label('title_text', __('Title Text'), ['class' => 'col-form-label']) }}
                                            {{ Form::text('title_text', null, ['class' => 'form-control', 'placeholder' => __('Enter Title Text')]) }}

                                            @error('title_text')
                                                <span class="invalid-title_text" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group col-md-6">
                                            {{ Form::label('footer_text', __('Footer Text'), ['class' => 'col-form-label']) }}
                                            {{ Form::text('footer_text', null, ['class' => 'form-control', 'placeholder' => __('Footer Text')]) }}
                                            @error('footer_text')
                                                <span class="invalid-footer_text" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group col-md-3">
                                            {{ Form::label('default_language', __('Default Language'), ['class' => 'col-form-label']) }}
                                            <select name="default_language" id="default_language" class="form-control">
                                                {{-- @foreach (\App\Models\Utility::languages() as $language)
                                                    <option @if ($lang == $language) selected @endif
                                                        value="{{ $language }}">{{ Str::upper($language) }}
                                                    </option>
                                                @endforeach --}}
                                                @foreach (App\Models\Utility::languages() as $code => $language)
                                                    <option @if ($lang == $code) selected @endif
                                                        value="{{ $code }}">{{ Str::ucfirst($language) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-3 ">
                                            <div class="col switch-width">
                                                <div class="form-group ml-2 mr-3">

                                                    {{ Form::label('display_landing_page', __('Enable Landing Page'), ['class' => 'col-form-label']) }}
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" data-toggle="switchbutton"
                                                            data-onstyle="primary" class=""
                                                            name="display_landing_page" id="display_landing_page"
                                                            {{ $settings['display_landing_page'] == 'on' ? 'checked="checked"' : '' }}>
                                                        <label class="custom-control-label mb-1"
                                                            for="display_landing_page"></label>
                                                    </div>


                                                </div>
                                            </div>
                                        </div>
                                        {{-- <div class="col-md-3 ">
                                            <div class="col switch-width">
                                                <div class="form-group ml-2 mr-3">
                                                    {{ Form::label('SITE_RTL', __('Enable RTL'), ['class' => 'col-form-label']) }}
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" data-toggle="switchbutton"
                                                            data-onstyle="primary" class="" name="SITE_RTL"
                                                            id="SITE_RTL" value="on"
                                                            {{ env('SITE_RTL') == 'on' ? 'checked="checked"' : '' }}>
                                                        <label class="custom-control-label mb-1" for="SITE_RTL"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> --}}

                                        <div class="col-sm-3 ">
                                            <div class="col switch-width">
                                                <div class="form-group ml-2 mr-3">
                                                    {{ Form::label('SITE_RTL', __('Enable RTL'), ['class' => 'col-form-label']) }}
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" data-toggle="switchbutton"
                                                            data-onstyle="primary" class="" name="SITE_RTL"
                                                            id="SITE_RTL"
                                                            {{ \App\Models\Utility::getValByName('SITE_RTL') == 'on' ? 'checked="checked"' : '' }}>
                                                        <label class="custom-control-label mb-1" for="SITE_RTL"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <h5 class="mt-3 mb-3">{{ __('Theme Customizer') }}</h5>
                                        <div class="col-12">
                                            <div class="pct-body">
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <h6 class="">
                                                            <i data-feather="credit-card"
                                                                class="me-2"></i>{{ __('Primary color Settings') }}
                                                        </h6>
                                                        <hr class="my-2" />
                                                        <div class="color-wrp">
                                                            <div class="theme-color themes-color">
                                                                <a href="#!"
                                                                    class="themes-color-change {{ $color == 'theme-1' ? 'active_color' : '' }}"
                                                                    data-value="theme-1"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-1"
                                                                    {{ $color == 'theme-1' ? 'checked' : '' }}>
                                                                <a href="#!"
                                                                    class="themes-color-change {{ $color == 'theme-2' ? 'active_color' : '' }}"
                                                                    data-value="theme-2"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-2"
                                                                    {{ $color == 'theme-2' ? 'checked' : '' }}>
                                                                <a href="#!"
                                                                    class="themes-color-change {{ $color == 'theme-3' ? 'active_color' : '' }}"
                                                                    data-value="theme-3"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-3"
                                                                    {{ $color == 'theme-3' ? 'checked' : '' }}>
                                                                <a href="#!"
                                                                    class="themes-color-change {{ $color == 'theme-4' ? 'active_color' : '' }}"
                                                                    data-value="theme-4"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-4"
                                                                    {{ $color == 'theme-4' ? 'checked' : '' }}>
                                                                <a href="#!"
                                                                    class="themes-color-change {{ $color == 'theme-5' ? 'active_color' : '' }}"
                                                                    data-value="theme-5"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-5"
                                                                    {{ $color == 'theme-5' ? 'checked' : '' }}>
                                                                <br>
                                                                <a href="#!"
                                                                    class="themes-color-change {{ $color == 'theme-6' ? 'active_color' : '' }}"
                                                                    data-value="theme-6"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-6"
                                                                    {{ $color == 'theme-6' ? 'checked' : '' }}>
                                                                <a href="#!"
                                                                    class="themes-color-change {{ $color == 'theme-7' ? 'active_color' : '' }}"
                                                                    data-value="theme-7"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-7"
                                                                    {{ $color == 'theme-7' ? 'checked' : '' }}>
                                                                <a href="#!"
                                                                    class="themes-color-change {{ $color == 'theme-8' ? 'active_color' : '' }}"
                                                                    data-value="theme-8"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-8"
                                                                    {{ $color == 'theme-8' ? 'checked' : '' }}>
                                                                <a href="#!"
                                                                    class="themes-color-change {{ $color == 'theme-9' ? 'active_color' : '' }}"
                                                                    data-value="theme-9"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-9"
                                                                    {{ $color == 'theme-9' ? 'checked' : '' }}>
                                                                <a href="#!"
                                                                    class="themes-color-change {{ $color == 'theme-10' ? 'active_color' : '' }}"
                                                                    data-value="theme-10"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-10"
                                                                    {{ $color == 'theme-10' ? 'checked' : '' }}>
                                                            </div>
                                                            <div class="color-picker-wrp">
                                                                <input type="color" value="{{ $color ? $color : '' }}"
                                                                    class="colorPicker {{ isset($settings['color_flag']) && $settings['color_flag'] == 'true' ? 'active_color' : '' }} image-input"
                                                                    name="custom_color" data-bs-toggle="tooltip"
                                                                    data-bs-placement="top" id="color-picker">
                                                                <input type="hidden" name="custom-color" id="colorCode">
                                                                <input type='hidden' name="color_flag"
                                                                    value={{ isset($settings['color_flag']) && $settings['color_flag'] == 'true' ? 'true' : 'false' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <h6 class=" ">
                                                            <i data-feather="layout"
                                                                class="me-2"></i>{{ __('Sidebar Settings') }}
                                                        </h6>
                                                        <hr class="my-2 " />
                                                        <div class="form-check form-switch ">
                                                            <input type="checkbox" class="form-check-input"
                                                                id="is_sidebar_transperent" name="is_sidebar_transperent"
                                                                @if ($is_sidebar_transperent == 'on') checked @endif />

                                                            <label class="form-check-label f-w-600 pl-1"
                                                                for="is_sidebar_transperent">{{ __('Transparent layout') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <h6 class=" ">
                                                            <i data-feather="sun"
                                                                class=""></i>{{ __('Layout Settings') }}
                                                        </h6>
                                                        <hr class=" my-2  " />
                                                        <div class="form-check form-switch mt-2 ">
                                                            <input type="checkbox" class="form-check-input"
                                                                id="dark_mode" name="dark_mode"
                                                                @if ($dark_mode == 'on') checked @endif />

                                                            <label class="form-check-label f-w-600 pl-1"
                                                                for="dark_mode">{{ __('Dark Layout') }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button class="btn-submit btn btn-primary" type="submit">
                                        {{ __('Save Changes') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>


                <div class="" id="company-settings">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Company Settings') }}</h5>
                        </div>
                        {{ Form::model($settings, ['route' => 'company.settings', 'method' => 'post']) }}
                        <div class="card-body">

                            <div class="row">
                                <div class="form-group col-md-4">
                                    {{ Form::label('company_name', __('Company Name'), ['class' => 'col-form-label']) }}<x-required></x-required>
                                    {{ Form::text('company_name', null, ['class' => 'form-control', 'placeholder' => __('Enter Company Name')]) }}

                                    @error('company_name')
                                        <span class="invalid-company_name" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror

                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('company_address', __('Address'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('company_address', null, ['class' => 'form-control ', 'placeholder' => __('Enter Address')]) }}
                                    @error('company_address')
                                        <span class="invalid-company_address" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('company_city', __('City'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('company_city', null, ['class' => 'form-control ', 'placeholder' => __('Enter City')]) }}
                                    @error('company_city')
                                        <span class="invalid-company_city" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('company_state', __('State'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('company_state', null, ['class' => 'form-control ', 'placeholder' => __('Enter State')]) }}
                                    @error('company_state')
                                        <span class="invalid-company_state" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('company_zipcode', __('Zip/Post Code'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('company_zipcode', null, ['class' => 'form-control', 'placeholder' => __('Enter Zip/Post Code')]) }}
                                    @error('company_zipcode')
                                        <span class="invalid-company_zipcode" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('company_country', __('Country'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('company_country', null, ['class' => 'form-control ', 'placeholder' => __('Enter Country')]) }}
                                    @error('company_country')
                                        <span class="invalid-company_country" role="alert"><strong
                                                class="text-danger">{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('company_telephone', __('Telephone'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('company_telephone', null, ['class' => 'form-control', 'placeholder' => __('Enter Telephone')]) }}
                                    @error('company_telephone')
                                        <span class="invalid-company_telephone" role="alert"><strong
                                                class="text-danger">{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                {{-- <div class="form-group col-md-4">
                                    {{ Form::label('company_email', __('System Email'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('company_email', null, ['class' => 'form-control', 'placeholder' => 'Enter System Email']) }}
                                    @error('company_email')
                                        <span class="invalid-company_email" role="alert"><strong
                                                class="text-danger">{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('company_email_from_name', __('Email (From Name)'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('company_email_from_name', null, ['class' => 'form-control ', 'placeholder' => 'Enter Email']) }}
                                    @error('company_email_from_name')
                                        <span class="invalid-company_email_from_name" role="alert"><strong
                                                class="text-danger">{{ $message }}</strong></span>
                                    @enderror
                                </div> --}}


                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            {{ Form::label('company_start_time', __('Company Start Time'), ['class' => 'col-form-label']) }}<x-required></x-required>

                                            {{ Form::time('company_start_time', null, ['class' => 'form-control timepicker_format']) }}
                                            @error('company_start_time')
                                                <span class="invalid-company_start_time" role="alert">
                                                    <small class="text-danger">{{ $message }}</small>
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-6">
                                            {{ Form::label('company_end_time', __('Company End Time'), ['class' => 'col-form-label']) }}<x-required></x-required>
                                            {{ Form::time('company_end_time', null, ['class' => 'form-control timepicker_format']) }}
                                            @error('company_end_time')
                                                <span class="invalid-company_end_time" role="alert">
                                                    <small class="text-danger">{{ $message }}</small>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Form::label('timezone', __('Timezone'), ['class' => 'col-form-label text-dark']) }}
                                    <select type="text" name="timezone" class="form-control select2" id="timezone">
                                        <option value="">{{ __('Select Timezone') }}</option>
                                        @foreach ($timezones as $k => $timezone)
                                            <option value="{{ $k }}"
                                                {{ $settings['timezone'] == $k ? 'selected' : '' }}>
                                                {{ $timezone }}</option>
                                        @endforeach
                                    </select>
                                    @error('timezone')
                                        <span class="invalid-timezone" role="alert">
                                            <small class="text-danger">{{ $message }}</small>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="" for="ip_restrict">{{ __('Ip Restrict') }}</label>
                                    <div class="custom-control custom-switch mt-3">
                                        <input type="checkbox" class=" form-check-input" data-toggle="switchbutton"
                                            data-onstyle="primary" name="ip_restrict" id="ip_restrict"
                                            {{ $settings['ip_restrict'] == 'on' ? 'checked="checked"' : '' }}>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn-submit btn btn-primary" type="submit">
                                {{ __('Save Changes') }}
                            </button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>



                <div class="" id="system-settings">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('System Settings') }}</h5>
                        </div>
                        {{ Form::model($settings, ['route' => 'system.settings', 'method' => 'post']) }}
                        <div class="card-body">
                            <div class="row company-setting">
                                <div class="form-group col-md-4">
                                    {{ Form::label('site_currency', __('Currency'), ['class' => 'col-form-label']) }}<x-required></x-required>
                                    {{ Form::text('site_currency', null, ['class' => 'form-control', 'placeholder' => __('Enter Currency')]) }}
                                    <small class="text-xs">
                                        {{ __('Note: Add currency code as per three-letter ISO code') }}.
                                        <a href="https://stripe.com/docs/currencies"
                                            target="_blank">{{ __('You can find out how to do that here.') }}</a>
                                    </small>
                                    @error('site_currency')
                                        <br>
                                        <span class="text-xs text-danger invalid-site_currency"
                                            role="alert">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('site_currency_symbol', __('Currency Symbol'), ['class' => 'col-form-label']) }}<x-required></x-required>
                                    {{ Form::text('site_currency_symbol', null, ['class' => 'form-control', 'placeholder' => __('Enter Currency Symbol')]) }}
                                    @error('site_currency_symbol')
                                        <span class="text-xs text-danger invalid-site_currency_symbol"
                                            role="alert">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="col-form-label">{{ __('Currency Symbol Position') }}</label>
                                    <div class="form-check form-check">
                                        <input class="form-check-input" type="radio" id="pre" value="pre"
                                            name="site_currency_symbol_position"
                                            @if ($settings['site_currency_symbol_position'] == 'pre') checked @endif>
                                        <label class="form-check-label" for="pre">
                                            {{ __('Pre') }}
                                        </label>
                                    </div>
                                    <div class="form-check form-check">
                                        <input class="form-check-input" type="radio" id="post" value="post"
                                            name="site_currency_symbol_position"
                                            @if ($settings['site_currency_symbol_position'] == 'post') checked @endif>
                                        <label class="form-check-label" for="post">
                                            {{ __('Post') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="site_date_format" class="col-form-label">{{ __('Date Format') }}</label>
                                    <select type="text" name="site_date_format" class="form-control"
                                        id="site_date_format">
                                        <option value="M j, Y"
                                            @if (@$settings['site_date_format'] == 'M j, Y') selected="selected" @endif>
                                            Jan 1,2015</option>
                                        <option value="d-m-Y"
                                            @if (@$settings['site_date_format'] == 'd-m-Y') selected="selected" @endif>
                                            dd-mm-yyyy</option>
                                        <option value="m-d-Y"
                                            @if (@$settings['site_date_format'] == 'm-d-Y') selected="selected" @endif>
                                            mm-dd-yyyy</option>
                                        <option value="Y-m-d"
                                            @if (@$settings['site_date_format'] == 'Y-m-d') selected="selected" @endif>
                                            yyyy-mm-dd</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="site_time_format" class="col-form-label">{{ __('Time Format') }}</label>
                                    <select type="text" name="site_time_format" class="form-control"
                                        id="site_time_format">
                                        <option value="g:i A"
                                            @if (@$settings['site_time_format'] == 'g:i A') selected="selected" @endif>
                                            10:30 PM</option>
                                        <option value="g:i a"
                                            @if (@$settings['site_time_format'] == 'g:i a') selected="selected" @endif>
                                            10:30 pm</option>
                                        <option value="H:i"
                                            @if (@$settings['site_time_format'] == 'H:i') selected="selected" @endif>
                                            22:30 am</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    {{-- {{Form::label('bug_prefix',__('Bug Prefix'),['class'=>'col-form-label']) }}
                                    {{Form::text('bug_prefix',null,array('class'=>'form-control'))}}
                                    @error('bug_prefix')
                                    <span class="text-xs text-danger invalid-bug_prefix" role="alert">{{ $message }}</span>
                                    @enderror --}}


                                    {{ Form::label('employee_prefix', __('Employee Prefix'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('employee_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Employee Prefix')]) }}
                                    @error('employee_prefix')
                                        <span class="text-xs text-danger invalid-employee_prefix" role="alert">
                                            <small class="text-danger">{{ $message }}</small>
                                        </span>
                                    @enderror

                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn-submit btn btn-primary" type="submit">
                                {{ __('Save Changes') }}
                            </button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                <div class="" id="email-settings">
                    {{ Form::open(['route' => 'email.settings', 'method' => 'post']) }}
                    <div class="row">
                        <div class="col-lg-12 col-sm-12 col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ __('Email Settings') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            {{ Form::label('mail_driver', __('Mail Driver'), ['class' => 'col-form-label']) }}
                                            {{ Form::text('mail_driver', $setting['mail_driver'], ['class' => 'form-control', 'placeholder' => __('Enter Mail Driver')]) }}
                                            @error('mail_driver')
                                                <span class="text-xs text-danger invalid-mail_driver"
                                                    role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            {{ Form::label('mail_host', __('Mail Host'), ['class' => 'col-form-label']) }}
                                            {{ Form::text('mail_host', $setting['mail_host'], ['class' => 'form-control ', 'placeholder' => __('Enter Mail Host')]) }}
                                            @error('mail_host')
                                                <span class="text-xs text-danger invalid-mail_driver"
                                                    role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            {{ Form::label('mail_port', __('Mail Port'), ['class' => 'col-form-label']) }}
                                            {{ Form::text('mail_port', $setting['mail_port'], ['class' => 'form-control', 'placeholder' => __('Enter Mail Port')]) }}
                                            @error('mail_port')
                                                <span class="text-xs text-danger invalid-mail_port"
                                                    role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            {{ Form::label('mail_username', __('Mail Username'), ['class' => 'col-form-label']) }}
                                            {{ Form::text('mail_username', $setting['mail_username'], ['class' => 'form-control', 'placeholder' => __('Enter Mail Username')]) }}
                                            @error('mail_username')
                                                <span class="text-xs text-danger invalid-mail_username"
                                                    role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            {{ Form::label('mail_password', __('Mail Password'), ['class' => 'col-form-label']) }}
                                            {{ Form::text('mail_password', $setting['mail_password'], ['class' => 'form-control', 'placeholder' => __('Enter Mail Password')]) }}
                                            @error('mail_password')
                                                <span class="text-xs text-danger invalid-mail_password"
                                                    role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            {{ Form::label('mail_encryption', __('Mail Encryption'), ['class' => 'col-form-label']) }}
                                            {{ Form::text('mail_encryption', $setting['mail_encryption'], ['class' => 'form-control', 'placeholder' => __('Enter Mail Encryption')]) }}
                                            @error('mail_encryption')
                                                <span class="text-xs text-danger invalid-mail_encryption"
                                                    role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            {{ Form::label('mail_from_address', __('Mail From Address'), ['class' => 'col-form-label']) }}
                                            {{ Form::text('mail_from_address', $setting['mail_from_address'], ['class' => 'form-control', 'placeholder' => __('Enter Mail From Address')]) }}
                                            @error('mail_from_address')
                                                <span class="text-xs text-danger invalid-mail_from_address"
                                                    role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            {{ Form::label('mail_from_name', __('Mail From Name'), ['class' => 'col-form-label']) }}
                                            {{ Form::text('mail_from_name', $setting['mail_from_name'], ['class' => 'form-control', 'placeholder' => __('Enter Mail From Name')]) }}
                                            @error('mail_from_name')
                                                <span class="text-xs text-danger invalid-mail_from_name"
                                                    role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="row">
                                        <div class="col-md-6">
                                            {{-- <a href="#" class="btn btn-xs btn-primary" data-ajax-popup="true"
                                                data-title="{{ __('Send Test Mail') }}"
                                                data-url="{{ route('test.mail') }}">
                                                {{ __('Send Test Mail') }}
                                            </a> --}}
                                            <a href="#" class="btn btn-print-invoice  btn-primary m-r-10 send_email"
                                                data-ajax-popup="true" data-title="{{ __('Send Test Mail') }}"
                                                data-url="{{ route('test.mail') }}">
                                                {{ __('Send Test Mail') }}
                                            </a>

                                        </div>
                                        <div class="text-end col-md-6">
                                            {{ Form::submit(__('Save Changes'), ['class' => 'btn btn-xs btn-primary']) }}
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>



                <div class="" id="pusher-settings">
                    {{ Form::open(['route' => 'pusher.settings', 'method' => 'post']) }}
                    <div class="row">
                        <div class="col-lg-12 col-sm-12 col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-lg-8 col-md-8 col-sm-8">
                                            <h5>{{ __('Pusher Settings') }}</h5><small
                                                class="text-secondary font-weight-bold"></small>
                                        </div>

                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                                            <label for="pusher_app_id"
                                                class="col-form-label">{{ __('Pusher App Id') }}</label>
                                            <input class="form-control" placeholder="{{__('Enter Pusher App Id')}}"
                                                name="pusher_app_id" type="text"
                                                value="{{ $setting['pusher_app_id'] ?? '' }}" id="pusher_app_id">

                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                                            <label for="pusher_app_key"
                                                class="col-form-label">{{ __('Pusher App Key') }}</label>
                                            <input class="form-control " placeholder="{{__('Enter Pusher App Key')}}"
                                                name="pusher_app_key" type="text"
                                                value="{{ $setting['pusher_app_key'] ?? '' }}" id="pusher_app_key">

                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                                            <label for="pusher_app_secret"
                                                class="col-form-label">{{ __('Pusher App Secret') }}</label>
                                            <input class="form-control " placeholder="{{__('Enter Pusher App Secret')}}"
                                                name="pusher_app_secret" type="text"
                                                value="{{ $setting['pusher_app_secret'] ?? '' }}" id="pusher_app_secret">

                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                                            <label for="pusher_app_cluster"
                                                class="col-form-label">{{ __('Pusher App Cluster') }}</label>
                                            <input class="form-control " placeholder="{{__('Enter Pusher App Cluster')}}"
                                                name="pusher_app_cluster" type="text"
                                                value="{{ $setting['pusher_app_cluster'] ?? '' }}" id="pusher_app_cluster">

                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">

                                    {{ Form::submit(__('Save Changes'), ['class' => 'btn btn-xs btn-primary']) }}

                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>

                {{-- <div class="" id="email-notification-settings">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Email Notification Settings') }}</h5>
                        </div>
                        <div class="card-body table-border-style ">
                            <div class="table-responsive">
                                <table class="table" id="pc-dt-simple">
                                    <thead>
                                        <tr>
                                            <th class="w-75"> {{ __('Name') }}</th>
                                            <th class="text-center"> {{ __('On/Off') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($EmailTemplates as $EmailTemplate)
                                            <tr class="">
                                                <td>{{ $EmailTemplate->name }}</td>
                                                <td class="text-center">

                                                    <div class="form-group col-md-12">
                                                        <label class="form-check form-switch d-inline-block">
                                                            <input type="checkbox"
                                                                class="email-template-checkbox form-check-input"
                                                                name="{{ $EmailTemplate->id }}"
                                                                @if ($EmailTemplate->template->is_active == 1) checked="checked" @endif
                                                                value="{{ $EmailTemplate->template->is_active == 1 ? '1' : '0' }}"
                                                                data-url="{{ route('company.email.setting', $EmailTemplate->id) }}">
                                                            <span class="slider1 round"></span>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div> --}}

                <!--Email Notification Setting-->
                <div id="email-notification-settings" class="card">

                    {{ Form::model($settings, ['route' => ['company.email.setting'], 'method' => 'post']) }}
                    @csrf
                    <div class="col-md-12">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-lg-8 col-md-8 col-sm-8">
                                    <h5>{{ __('Email Notification Settings') }}</h5>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <!-- <div class=""> -->
                                @foreach ($EmailTemplates as $EmailTemplate)
                                    <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                        <div class="list-group">
                                            <div class="list-group-item form-switch form-switch-right">
                                                <label class="form-label"
                                                    style="margin-left:5%;">{{ $EmailTemplate->name }}</label>

                                                <input class="form-check-input" name='{{ $EmailTemplate->id }}'
                                                    id="email_tempalte_{{ $EmailTemplate->template->id }}"
                                                    type="checkbox"
                                                    @if ($EmailTemplate->template->is_active == 1) checked="checked" @endif
                                                    type="checkbox" value="1"
                                                    data-url="{{ route('company.email.setting', [$EmailTemplate->template->id]) }}" />
                                                <label class="form-check-label"
                                                    for="email_tempalte_{{ $EmailTemplate->template->id }}"></label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <!-- </div> -->
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn-submit btn btn-primary" type="submit">
                                {{ __('Save Changes') }}
                            </button>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>

                <div class="" id="ip-restriction-settings">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">

                            <h5>{{ __('IP Restriction Settings') }}</h5>

                            <a data-url="{{ route('create.ip') }}" class="btn btn-sm btn-primary"
                                data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"
                                data-bs-placement="top" data-size="md" data-ajax-popup="true"
                                data-title="{{ __('Create New IP') }}">
                                <i class="ti ti-plus"></i>
                            </a>

                        </div>
                        <div class="card-body table-border-style ">
                            <div class="table-responsive">
                                <table class="table" id="pc-dt-simple">
                                    <thead>
                                        <tr>
                                            <th class="w-75"> {{ __('IP') }}</th>
                                            <th width="200px"> {{ 'Action' }}</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($ips as $ip)
                                            <tr class="Action">
                                                <td class="sorting_1">{{ $ip->ip }}</td>
                                                <td class="">
                                                    @can('Manage Company Settings')
                                                        <div class="action-btn me-2">
                                                            <a class="mx-3 btn btn-sm bg-info align-items-center"
                                                                data-url="{{ route('edit.ip', $ip->id) }}" data-size="md"
                                                                data-ajax-popup="true" data-title="{{ __('Edit IP') }}"
                                                                data-bs-toggle="tooltip" class="edit-icon"
                                                                data-bs-placement="top" title="{{ __('Edit') }}"><span class="text-white"><i
                                                                    class="ti ti-pencil "></i></span></a>
                                                        </div>
                                                    @endcan
                                                    @can('Manage Company Settings')
                                                        <div class="action-btn">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['destroy.ip', $ip->id], 'id' => 'delete-form-' . $ip->id]) !!}
                                                            <a href="#!"
                                                                class="mx-3 btn btn-sm bg-danger align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="{{ __('Delete') }}">
                                                                <span class="text-white"><i class="ti ti-trash "></i></span></a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                @if (Auth::user()->type == 'company')
                    <div class="" id="zoom-meeting-settings">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __('Zoom Meeting Settings') }}</h5>
                            </div>
                            {{ Form::open(['route' => 'zoom.settings', 'method' => 'post']) }}
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                        {{ Form::label('zoom_account_id', __('Zoom Account ID'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('zoom_account_id', isset($settings['zoom_account_id']) ? $settings['zoom_account_id'] : '', ['class' => 'form-control ', 'placeholder' => __('Enter Zoom Account ID')]) }}
                                    </div>

                                    <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                        {{ Form::label('zoom_client_id', __('Zoom Client ID'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('zoom_client_id', isset($settings['zoom_client_id']) ? $settings['zoom_client_id'] : '', ['class' => 'form-control ', 'placeholder' => __('Enter Zoom Client ID')]) }}
                                    </div>

                                    <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                        {{ Form::label('zoom_client_secret', __('Zoom Client Secret Key'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('zoom_client_secret', isset($settings['zoom_client_secret']) ? $settings['zoom_client_secret'] : '', ['class' => 'form-control ', 'placeholder' => __('Enter Zoom Client Secret Key')]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button class="btn-submit btn btn-primary" type="submit">
                                    {{ __('Save Changes') }}
                                </button>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                    <div class="" id="slack-settings">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __('Slack Settings') }}</h5>
                                <small
                                    class="text-secondary font-weight-bold">{{ __('Slack Notification Settings') }}</small>
                            </div>
                            {{ Form::open(['route' => 'slack.setting', 'id' => 'slack-setting', 'method' => 'post', 'class' => 'd-contents']) }}
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                        {{ Form::label('Slack Webhook URL', __('Slack Webhook URL'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('slack_webhook', isset($settings['slack_webhook']) ? $settings['slack_webhook'] : '', ['class' => 'form-control w-100', 'placeholder' => __('Enter Slack Webhook URL'), 'required' => 'required']) }}
                                    </div>
                                    {{-- <div class="col-lg-12 col-md-12 col-sm-12 form-group mb-3">
                                        {{ Form::label('Module Setting', __('Module Setting'), ['class' => 'col-form-label']) }}
                                    </div> --}}
                                    <div class="col-md-4">
                                        <ul class="list-group">
                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Monthly payslip create', __('New Monthly Payslip'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('monthly_payslip_notification', '1', isset($settings['monthly_payslip_notification']) && $settings['monthly_payslip_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'monthly_payslip_notification']) }}
                                                    <label class="col-form-label"
                                                        for="monthly_payslip_notification"></label>
                                                </div>
                                            </li>

                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Award create', __('New Award'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('award_notification', '1', isset($settings['award_notification']) && $settings['award_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'award_notification']) }}
                                                    <label class="col-form-label" for="award_notification"></label>
                                                </div>
                                            </li>

                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Ticket create', __('New Ticket'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('ticket_notification', '1', isset($settings['ticket_notification']) && $settings['ticket_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'ticket_notification']) }}
                                                    <label class="col-form-label" for="ticket_notification"></label>
                                                </div>
                                            </li>


                                        </ul>
                                    </div>

                                    <div class="col-md-4">
                                        <ul class="list-group">
                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Announcement create', __('New Announcement'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">

                                                    {{ Form::checkbox('Announcement_notification', '1', isset($settings['Announcement_notification']) && $settings['Announcement_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'Announcement_notification']) }}
                                                    <label class="col-form-label" for="Announcement_notification"></label>
                                                </div>
                                            </li>

                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Holidays create', __('New Holidays'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('Holiday_notification', '1', isset($settings['Holiday_notification']) && $settings['Holiday_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'Holiday_notification']) }}
                                                    <label class="col-form-label" for="Holiday_notification"></label>
                                                </div>
                                            </li>

                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Event create', __('New Event'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('event_notification', '1', isset($settings['event_notification']) && $settings['event_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'event_notification']) }}
                                                    <label class="col-form-label" for="event_notification"></label>
                                                </div>
                                            </li>


                                        </ul>
                                    </div>

                                    <div class="col-md-4">
                                        <ul class="list-group">
                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Meeting create', __('New Meeting'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('meeting_notification', '1', isset($settings['meeting_notification']) && $settings['meeting_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'meeting_notification']) }}
                                                    <label class="col-form-label" for="meeting_notification"></label>
                                                </div>
                                            </li>

                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Company policy create', __('New Company Policy'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('company_policy_notification', '1', isset($settings['company_policy_notification']) && $settings['company_policy_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'company_policy_notification']) }}
                                                    <label class="col-form-label"
                                                        for="company_policy_notification"></label>
                                                </div>
                                            </li>

                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Contract create', __('New Contract'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('contract_notification', '1', isset($settings['contract_notification']) && $settings['contract_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'contract_notification']) }}
                                                    <label class="col-form-label" for="contract_notification"></label>
                                                </div>
                                            </li>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button class="btn-submit btn btn-primary" type="submit">
                                    {{ __('Save Changes') }}
                                </button>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>



                    <div class="" id="telegram-settings">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __('Telegram Settings') }}</h5>
                                <small
                                    class="text-secondary font-weight-bold">{{ __('Telegram Notification Settings') }}</small>
                            </div>
                            {{ Form::open(['route' => 'telegram.setting', 'id' => 'telegram-setting', 'method' => 'post', 'class' => 'd-contents']) }}
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                                        {{ Form::label('Telegram Access Token', __('Telegram Access Token'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('telegram_accestoken', isset($settings['telegram_accestoken']) ? $settings['telegram_accestoken'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Telegram AccessToken'), 'required' => 'required']) }}
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                                        {{ Form::label('Telegram ChatID', __('Telegram ChatID'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('telegram_chatid', isset($settings['telegram_chatid']) ? $settings['telegram_chatid'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Telegram ChatID'), 'required' => 'required']) }}
                                    </div>
                                    <div class="col-md-4">
                                        <ul class="list-group">
                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Monthly payslip create', __('New Monthly Payslip '), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('telegram_monthly_payslip_notification', '1', isset($settings['telegram_monthly_payslip_notification']) && $settings['telegram_monthly_payslip_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_monthly_payslip_notification']) }}
                                                    <label class="col-form-label"
                                                        for="telegram_monthly_payslip_notification"></label>
                                                </div>
                                            </li>

                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Award create', __('New Award'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('telegram_award_notification', '1', isset($settings['telegram_award_notification']) && $settings['telegram_award_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_award_notification']) }}
                                                    <label class="col-form-label"
                                                        for="telegram_award_notification"></label>
                                                </div>
                                            </li>

                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Ticket create', __('New Ticket'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('telegram_ticket_notification', '1', isset($settings['telegram_ticket_notification']) && $settings['telegram_ticket_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_ticket_notification']) }}
                                                    <label class="col-form-label"
                                                        for="telegram_ticket_notification"></label>
                                                </div>
                                            </li>


                                        </ul>
                                    </div>

                                    <div class="col-md-4">
                                        <ul class="list-group">
                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Announcement create', __('New Announcement'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('telegram_Announcement_notification', '1', isset($settings['telegram_Announcement_notification']) && $settings['telegram_Announcement_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_Announcement_notification']) }}
                                                    <label class="col-form-label"
                                                        for="telegram_Announcement_notification"></label>
                                                </div>
                                            </li>

                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Holidays create', __('New Holidays'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('telegram_Holiday_notification', '1', isset($settings['telegram_Holiday_notification']) && $settings['telegram_Holiday_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_Holiday_notification']) }}
                                                    <label class="col-form-label"
                                                        for="telegram_Holiday_notification"></label>
                                                </div>
                                            </li>

                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Event create', __('New Event'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('telegram_event_notification', '1', isset($settings['telegram_event_notification']) && $settings['telegram_event_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_event_notification']) }}
                                                    <label class="col-form-label"
                                                        for="telegram_event_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="col-md-4">
                                        <ul class="list-group">
                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Meeting create', __('New Meeting '), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('telegram_meeting_notification', '1', isset($settings['telegram_meeting_notification']) && $settings['telegram_meeting_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_meeting_notification']) }}
                                                    <label class="col-form-label"
                                                        for="telegram_meeting_notification"></label>
                                                </div>
                                            </li>

                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Company policy create', __('New Company Policy '), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('telegram_company_policy_notification', '1', isset($settings['telegram_company_policy_notification']) && $settings['telegram_company_policy_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_company_policy_notification']) }}
                                                    <label class="col-form-label"
                                                        for="telegram_company_policy_notification"></label>
                                                </div>
                                            </li>

                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Contract create', __('New Contract'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('telegram_contract_notification', '1', isset($settings['telegram_contract_notification']) && $settings['telegram_contract_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_contract_notification']) }}
                                                    <label class="col-form-label"
                                                        for="telegram_contract_notification"></label>
                                                </div>
                                            </li>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button class="btn-submit btn btn-primary" type="submit">
                                    {{ __('Save Changes') }}
                                </button>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>

                    <div class="" id="twilio-settings">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __('Twilio Settings') }}</h5>
                                <small
                                    class="text-secondary font-weight-bold">{{ __('Twilio Notification Settings') }}</small>

                            </div>
                            {{ Form::open(['route' => 'twilio.setting', 'id' => 'twilio-setting', 'method' => 'post', 'class' => 'd-contents']) }}
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                                        {{ Form::label('Twilio SID', __('Twilio SID'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('twilio_sid', isset($settings['twilio_sid']) ? $settings['twilio_sid'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Twilio Sid'), 'required' => 'required']) }}
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                                        {{ Form::label('Twilio Token', __('Twilio Token'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('twilio_token', isset($settings['twilio_token']) ? $settings['twilio_token'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Twilio Token'), 'required' => 'required']) }}
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                                        {{ Form::label('Twilio From', __('Twilio From'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('twilio_from', isset($settings['twilio_from']) ? $settings['twilio_from'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Twilio From'), 'required' => 'required']) }}
                                    </div>
                                    <div class="col-lg-12 col-md-12 col-sm-12 form-group mb-3">
                                        {{-- {{ Form::label('Module Setting', __('Module Setting'), ['class' => 'col-form-label']) }} --}}
                                    </div>


                                    <div class="col-md-4">
                                        <ul class="list-group">
                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Payslip create', __('New Monthly Payslip'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('twilio_monthly_payslip_notification', '1', isset($settings['twilio_monthly_payslip_notification']) && $settings['twilio_monthly_payslip_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_monthly_payslip_notification']) }}
                                                    <label class="col-form-label"
                                                        for="twilio_monthly_payslip_notification"></label>
                                                </div>
                                            </li>

                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Leave Approve/Reject', __('Leave Approve/Reject'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('twilio_leave_approve_notification', '1', isset($settings['twilio_leave_approve_notification']) && $settings['twilio_leave_approve_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_leave_approve_notification']) }}
                                                    <label class="col-form-label"
                                                        for="twilio_leave_approve_notification"></label>
                                                </div>
                                            </li>

                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Ticket create', __('New Ticket '), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('twilio_ticket_notification', '1', isset($settings['twilio_ticket_notification']) && $settings['twilio_ticket_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_ticket_notification']) }}
                                                    <label class="col-form-label"
                                                        for="twilio_ticket_notification"></label>
                                                </div>
                                            </li>


                                        </ul>
                                    </div>

                                    <div class="col-md-4">
                                        <ul class="list-group">
                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Award create', __('New Award'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('twilio_award_notification', '1', isset($settings['twilio_award_notification']) && $settings['twilio_award_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_award_notification']) }}
                                                    <label class="col-form-label" for="twilio_award_notification"></label>
                                                </div>
                                            </li>

                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Trip create', __('New Trip '), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('twilio_trip_notification', '1', isset($settings['twilio_trip_notification']) && $settings['twilio_trip_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_trip_notification']) }}
                                                    <label class="col-form-label" for="twilio_trip_notification"></label>
                                                </div>
                                            </li>

                                        </ul>
                                    </div>

                                    <div class="col-md-4">
                                        <ul class="list-group">
                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Event create', __('New Event'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('twilio_event_notification', '1', isset($settings['twilio_event_notification']) && $settings['twilio_event_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_event_notification']) }}
                                                    <label class="col-form-label" for="twilio_event_notification"></label>
                                                </div>
                                            </li>

                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                                {{ Form::label('Announcement create', __('New Announcement'), ['class' => 'col-form-label']) }}
                                                <div class="form-check form-switch d-inline-block float-right">
                                                    {{ Form::checkbox('twilio_announcement_notification', '1', isset($settings['twilio_announcement_notification']) && $settings['twilio_announcement_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_announcement_notification']) }}
                                                    <label class="col-form-label"
                                                        for="twilio_announcement_notification"></label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button class="btn-submit btn btn-primary" type="submit">
                                    {{ __('Save Changes') }}
                                </button>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                @endif

                <div id="recaptcha-print-settings" class="card">
                    <div class="col-md-12">
                        <form method="POST" action="{{ route('recaptcha.settings.store') }}" accept-charset="UTF-8">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-lg-8 col-md-8 col-sm-8">
                                        <a href="https://phppot.com/php/how-to-get-google-recaptcha-site-and-secret-key/"
                                            target="_blank" class="text-blue">
                                            <h5 class="">{{ __('ReCaptcha settings') }}</h5><small
                                                class="text-primary font-weight-bold">({{ __('How to Get Google reCaptcha Site and Secret key') }})</small>
                                        </a>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-sm-4 text-end">
                                        <div class="col switch-width">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" data-toggle="switchbutton" data-onstyle="primary"
                                                    class="" name="recaptcha_module" id="recaptcha_module"
                                                    value="yes"
                                                    {{ $settings['recaptcha_module'] == 'yes' ? 'checked="checked"' : '' }}>
                                                <label class="custom-control-label form-control-label px-2"
                                                    for="recaptcha_module "></label><br>
                                                <a href="https://phppot.com/php/how-to-get-google-recaptcha-site-and-secret-key/"
                                                    target="_blank" class="text-blue">

                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">

                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group col switch-width">
                                            {{ Form::label('google_recaptcha_version', __('Google Recaptcha Version'), ['class' => ' col-form-label']) }}

                                            {{ Form::select('google_recaptcha_version', $google_recaptcha_version, isset($setting['google_recaptcha_version']) ? $setting['google_recaptcha_version'] : 'v2-checkbox', ['id' => 'google_recaptcha_version', 'class' => 'form-control choices', 'searchEnabled' => 'true']) }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                                        <label for="google_recaptcha_key"
                                            class="form-label">{{ __('Google Recaptcha Key') }}</label>
                                        <input class="form-control" placeholder="{{ __('Enter Google Recaptcha Key') }}"
                                            name="google_recaptcha_key" type="text"
                                            value="{{ $settings['google_recaptcha_key'] }}" id="google_recaptcha_key">

                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                                        <label for="google_recaptcha_secret"
                                            class="form-label">{{ __('Google Recaptcha Secret') }}</label>
                                        <input class="form-control "
                                            placeholder="{{ __('Enter Google Recaptcha Secret') }}"
                                            name="google_recaptcha_secret" type="text"
                                            value="{{ $settings['google_recaptcha_secret'] }}"
                                            id="google_recaptcha_secret">

                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">

                                {{ Form::submit(__('Save Changes'), ['class' => 'btn btn-xs btn-primary']) }}

                            </div>
                        </form>
                    </div>
                </div>

                <div class="" id="offer-letter-settings">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h5>{{ __('Offer Letter Settings') }}</h5>
                            <div class="d-flex justify-content-end drp-languages">
                                <ul class="list-unstyled mb-0 m-2">
                                    <li class="dropdown dash-h-item drp-language" style="margin-top: -19px;">
                                        <a class="dash-head-link dropdown-toggle arrow-none me-0"
                                            data-bs-toggle="dropdown" href="#" role="button"
                                            aria-haspopup="false" aria-expanded="false" id="dropdownLanguage">
                                            <span class="drp-text hide-mob text-primary">
                                                {{ Str::ucfirst($offerlangName->fullName) }}
                                            </span>
                                            <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                        </a>
                                        <div class="dropdown-menu dash-h-dropdown dropdown-menu-end"
                                            aria-labelledby="dropdownLanguage">
                                            {{-- @foreach ($currantLang as $offerlangs)
                                                <a href="{{ route('get.offerlatter.language', ['noclangs' => $noclang, 'explangs' => $explang, 'offerlangs' => $offerlangs, 'joininglangs' => $joininglang]) }}"
                                                    class="dropdown-item ms-1 {{ $offerlangs == $offerlang ? 'text-primary' : '' }}">{{ Str::upper($offerlangs) }}</a>
                                            @endforeach --}}
                                            @foreach (App\Models\Utility::languages() as $code => $offerlangs)
                                                <a href="{{ route('get.offerlatter.language', ['noclangs' => $noclang, 'explangs' => $explang, 'offerlangs' => $code, 'joininglangs' => $joininglang]) }}"
                                                    class="dropdown-item ms-1 {{ $offerlang == $code ? 'text-primary' : '' }}">
                                                    <span>{{ ucFirst($offerlangs) }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </li>
                                </ul>

                            </div>
                        </div>
                        <div class="card-body ">
                            <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>

                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="card">
                                    <div class="card-header card-body">
                                        <div class="row text-xs">
                                            <div class="row">
                                                <p class="col-4">{{ __('Applicant Name') }} : <span
                                                        class="pull-end text-primary">{applicant_name}</span></p>
                                                <p class="col-4">{{ __('Company Name') }} : <span
                                                        class="pull-right text-primary">{app_name}</span></p>
                                                <p class="col-4">{{ __('Job title') }} : <span
                                                        class="pull-right text-primary">{job_title}</span></p>
                                                <p class="col-4">{{ __('Job type') }} : <span
                                                        class="pull-right text-primary">{job_type}</span></p>
                                                <p class="col-4">{{ __('Proposed Start Date') }} : <span
                                                        class="pull-right text-primary">{start_date}</span></p>
                                                <p class="col-4">{{ __('Working Location') }} : <span
                                                        class="pull-right text-primary">{workplace_location}</span></p>
                                                <p class="col-4">{{ __('Days Of Week') }} : <span
                                                        class="pull-right text-primary">{days_of_week}</span></p>
                                                <p class="col-4">{{ __('Salary') }} : <span
                                                        class="pull-right text-primary">{salary}</span></p>
                                                <p class="col-4">{{ __('Salary Type') }} : <span
                                                        class="pull-right text-primary">{salary_type}</span></p>
                                                <p class="col-4">{{ __('Salary Duration') }} : <span
                                                        class="pull-end text-primary">{salary_duration}</span></p>
                                                <p class="col-4">{{ __('Offer Expiration Date') }} : <span
                                                        class="pull-right text-primary">{offer_expiration_date}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-border-style ">

                            {{ Form::open(['route' => ['offerlatter.update', $offerlang], 'method' => 'post']) }}

                            <div class="form-group col-12">
                                {{ Form::label('content', __(' Format'), ['class' => 'form-label text-dark']) }}
                                <textarea name="content" class="summernote-simple">{!! isset($currOfferletterLang->content) ? $currOfferletterLang->content : '' !!}</textarea>
                            </div>
                        </div>
                        <div class="card-footer text-end">

                            {{ Form::submit(__('Save Changes'), ['class' => 'btn  btn-primary']) }}
                        </div>

                        {{ Form::close() }}
                    </div>
                </div>

                <div class="" id="joining-letter-settings">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h5>{{ __('Joining Letter Settings') }}</h5>
                            <div class="d-flex justify-content-end drp-languages">
                                <ul class="list-unstyled mb-0 m-2">
                                    <li class="dropdown dash-h-item drp-language" style="margin-top: -19px;">
                                        <a class="dash-head-link dropdown-toggle arrow-none me-0"
                                            data-bs-toggle="dropdown" href="#" role="button"
                                            aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                            <span class="drp-text hide-mob text-primary">

                                                {{ Str::ucfirst($joininglangName->fullName) }}
                                            </span>
                                            <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                        </a>
                                        <div class="dropdown-menu dash-h-dropdown dropdown-menu-end"
                                            aria-labelledby="dropdownLanguage1">
                                            {{-- @foreach ($currantLang as $joininglangs)
                                                <a href="{{ route('get.joiningletter.language', ['noclangs' => $noclang, 'explangs' => $explang, 'offerlangs' => $offerlang, 'joininglangs' => $joininglangs]) }}"
                                                    class="dropdown-item {{ $joininglangs == $joininglang ? 'text-primary' : '' }}">{{ Str::upper($joininglangs) }}</a>
                                            @endforeach --}}
                                            @foreach (App\Models\Utility::languages() as $code => $joininglangs)
                                                <a href="{{ route('get.joiningletter.language', ['noclangs' => $noclang, 'explangs' => $explang, 'offerlangs' => $offerlang, 'joininglangs' => $code]) }}"
                                                    class="dropdown-item ms-1 {{ $joininglang == $code ? 'text-primary' : '' }}">
                                                    <span>{{ ucFirst($joininglangs) }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </li>

                                </ul>
                            </div>

                        </div>
                        <div class="card-body ">
                            <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>

                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="card">
                                    <div class="card-header card-body">
                                        <div class="row text-xs">
                                            <div class="row">
                                                <p class="col-4">{{ __('Applicant Name') }} : <span
                                                        class="pull-end text-primary">{date}</span></p>
                                                <p class="col-4">{{ __('Company Name') }} : <span
                                                        class="pull-right text-primary">{app_name}</span></p>
                                                <p class="col-4">{{ __('Employee Name') }} : <span
                                                        class="pull-right text-primary">{employee_name}</span></p>
                                                <p class="col-4">{{ __('Address') }} : <span
                                                        class="pull-right text-primary">{address}</span></p>
                                                <p class="col-4">{{ __('Designation') }} : <span
                                                        class="pull-right text-primary">{designation}</span></p>
                                                <p class="col-4">{{ __('Start Date') }} : <span
                                                        class="pull-right text-primary">{start_date}</span></p>
                                                <p class="col-4">{{ __('Branch') }} : <span
                                                        class="pull-right text-primary">{branch}</span></p>
                                                <p class="col-4">{{ __('Start Time') }} : <span
                                                        class="pull-end text-primary">{start_time}</span></p>
                                                <p class="col-4">{{ __('End Time') }} : <span
                                                        class="pull-right text-primary">{end_time}</span></p>
                                                <p class="col-4">{{ __('Number of Hours') }} : <span
                                                        class="pull-right text-primary">{total_hours}</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-border-style ">

                            {{ Form::open(['route' => ['joiningletter.update', $joininglang], 'method' => 'post']) }}
                            <div class="form-group col-12">
                                {{ Form::label('content', __(' Format'), ['class' => 'form-label text-dark']) }}
                                <textarea name="content" class="summernote-simple">{!! isset($currjoiningletterLang->content) ? $currjoiningletterLang->content : '' !!}</textarea>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            {{ Form::submit(__('Save Changes'), ['class' => 'btn  btn-primary']) }}
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>

                <div class="" id="experience-certificate-settings">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h5>{{ __('Certificate of Experience Settings') }}</h5>
                            <div class="d-flex justify-content-end drp-languages">
                                <ul class="list-unstyled mb-0 m-2">
                                    <li class="dropdown dash-h-item drp-language" style="margin-top: -19px;">
                                        <a class="dash-head-link dropdown-toggle arrow-none me-0"
                                            data-bs-toggle="dropdown" href="#" role="button"
                                            aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                            <span class="drp-text hide-mob text-primary">

                                                {{ Str::ucfirst($explangName->fullName) }}
                                            </span>
                                            <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                        </a>
                                        <div class="dropdown-menu dash-h-dropdown dropdown-menu-end"
                                            aria-labelledby="dropdownLanguage1">
                                            {{-- @foreach ($currantLang as $explangs)
                                                <a href="{{ route('get.experiencecertificate.language', ['noclangs' => $noclang, 'explangs' => $explangs, 'offerlangs' => $offerlang, 'joininglangs' => $joininglang]) }}"
                                                    class="dropdown-item {{ $explangs == $explang ? 'text-primary' : '' }}">{{ Str::upper($explangs) }}</a>
                                            @endforeach --}}
                                            @foreach (App\Models\Utility::languages() as $code => $explangs)
                                                <a href="{{ route('get.experiencecertificate.language', ['noclangs' => $noclang, 'explangs' => $code, 'offerlangs' => $offerlang, 'joininglangs' => $joininglang]) }}"
                                                    class="dropdown-item ms-1 {{ $explang == $code ? 'text-primary' : '' }}">
                                                    <span>{{ ucFirst($explangs) }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </li>

                                </ul>
                            </div>

                        </div>
                        <div class="card-body ">
                            <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>

                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="card">
                                    <div class="card-header card-body">
                                        <div class="row text-xs">
                                            <div class="row">
                                                <p class="col-4">{{ __('Company Name') }} : <span
                                                        class="pull-right text-primary">{app_name}</span></p>
                                                <p class="col-4">{{ __('Employee Name') }} : <span
                                                        class="pull-right text-primary">{employee_name}</span></p>
                                                <p class="col-4">{{ __('Date of Issuance') }} : <span
                                                        class="pull-right text-primary">{date}</span></p>
                                                <p class="col-4">{{ __('Designation') }} : <span
                                                        class="pull-right text-primary">{designation}</span></p>
                                                <p class="col-4">{{ __('Start Date') }} : <span
                                                        class="pull-right text-primary">{start_date}</span></p>
                                                <p class="col-4">{{ __('Branch') }} : <span
                                                        class="pull-right text-primary">{branch}</span></p>
                                                <p class="col-4">{{ __('Start Time') }} : <span
                                                        class="pull-end text-primary">{start_time}</span></p>
                                                <p class="col-4">{{ __('End Time') }} : <span
                                                        class="pull-right text-primary">{end_time}</span></p>
                                                <p class="col-4">{{ __('Number of Hours') }} : <span
                                                        class="pull-right text-primary">{total_hours}</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-border-style ">

                            {{ Form::open(['route' => ['experiencecertificate.update', $explang], 'method' => 'post']) }}
                            <div class="form-group col-12">
                                {{ Form::label('content', __(' Format'), ['class' => 'form-label text-dark']) }}
                                <textarea name="content" class="summernote-simple">{!! isset($curr_exp_cetificate_Lang->content) ? $curr_exp_cetificate_Lang->content : '' !!}</textarea>
                            </div>
                        </div>
                        <div class="card-footer text-end">

                            {{ Form::submit(__('Save Changes'), ['class' => 'btn  btn-primary']) }}
                        </div>

                        {{ Form::close() }}
                    </div>
                </div>

                <div class="" id="noc-settings">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h5>{{ __('No Objection Certificate Settings') }}</h5>
                            <div class="d-flex justify-content-end drp-languages">
                                <ul class="list-unstyled mb-0 m-2">
                                    <li class="dropdown dash-h-item drp-language" style="margin-top: -19px;">
                                        <a class="dash-head-link dropdown-toggle arrow-none me-0"
                                            data-bs-toggle="dropdown" href="#" role="button"
                                            aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                            <span class="drp-text hide-mob text-primary">

                                                {{ Str::ucfirst($noclangName->fullName) }}
                                            </span>
                                            <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                        </a>
                                        <div class="dropdown-menu dash-h-dropdown dropdown-menu-end"
                                            aria-labelledby="dropdownLanguage1">
                                            {{-- @foreach ($currantLang as $noclangs)
                                                <a href="{{ route('get.noc.language', ['noclangs' => $noclangs, 'explangs' => $explang, 'offerlangs' => $offerlang, 'joininglangs' => $joininglang]) }}"
                                                    class="dropdown-item {{ $noclangs == $noclang ? 'text-primary' : '' }}">{{ Str::upper($noclangs) }}</a>
                                            @endforeach --}}
                                            @foreach (App\Models\Utility::languages() as $code => $noclangs)
                                                <a href="{{ route('get.noc.language', ['noclangs' => $code, 'explangs' => $explang, 'offerlangs' => $offerlang, 'joininglangs' => $joininglang]) }}"
                                                    class="dropdown-item ms-1 {{ $noclang == $code ? 'text-primary' : '' }}">
                                                    <span>{{ ucFirst($noclangs) }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </li>

                                </ul>
                            </div>

                        </div>
                        <div class="card-body ">
                            <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>

                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="card">
                                    <div class="card-header card-body">
                                        <div class="row text-xs">
                                            <div class="row">
                                                <p class="col-4">{{ __('Date') }} : <span
                                                        class="pull-end text-primary">{date}</span></p>
                                                <p class="col-4">{{ __('Company Name') }} : <span
                                                        class="pull-right text-primary">{app_name}</span></p>
                                                <p class="col-4">{{ __('Employee Name') }} : <span
                                                        class="pull-right text-primary">{employee_name}</span></p>
                                                <p class="col-4">{{ __('Designation') }} : <span
                                                        class="pull-right text-primary">{designation}</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-border-style ">
                            {{ Form::open(['route' => ['noc.update', $noclang], 'method' => 'post']) }}
                            <div class="form-group col-12">
                                {{ Form::label('content', __(' Format'), ['class' => 'form-label text-dark']) }}
                                <textarea name="content" class="summernote-simple">{!! isset($currnocLang->content) ? $currnocLang->content : '' !!}</textarea>
                            </div>
                        </div>
                        <div class="card-footer text-end">

                            {{ Form::submit(__('Save Changes'), ['class' => 'btn  btn-primary']) }}
                        </div>

                        {{ Form::close() }}
                    </div>
                </div>


                <!--storage Setting-->
                <!-- <div id="storage-settings" class="card">
                    {{ Form::open(['route' => 'storage.setting.store', 'enctype' => 'multipart/form-data']) }}
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-8 col-md-8 col-sm-8">
                                <h5 class="">{{ __('Storage Settings') }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="pe-2">
                                <input type="radio" class="btn-check" name="storage_setting" id="local-outlined"
                                    autocomplete="off" {{ $setting['storage_setting'] == 'local' ? 'checked' : '' }}
                                    value="local" checked>
                                <label class="btn btn-outline-primary"
                                    for="local-outlined">{{ __('Local') }}</label>
                            </div>
                            <div class="pe-2">
                                <input type="radio" class="btn-check" name="storage_setting" id="s3-outlined"
                                    autocomplete="off" {{ $setting['storage_setting'] == 's3' ? 'checked' : '' }}
                                    value="s3">
                                <label class="btn btn-outline-primary" for="s3-outlined">
                                    {{ __('AWS S3') }}</label>
                            </div>

                            <div class="pe-2">
                                <input type="radio" class="btn-check" name="storage_setting" id="wasabi-outlined"
                                    autocomplete="off" {{ $setting['storage_setting'] == 'wasabi' ? 'checked' : '' }}
                                    value="wasabi">
                                <label class="btn btn-outline-primary"
                                    for="wasabi-outlined">{{ __('Wasabi') }}</label>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div
                                class="local-setting row mb-4 {{ $setting['storage_setting'] == 'local' ? ' ' : 'd-none' }}">
                                {{-- <h4 class="small-title">{{ __('Local Settings') }}</h4> --}}
                                <div class="form-group col-8 switch-width">
                                    {{ Form::label('local_storage_validation', __('Only Upload Files'), ['class' => ' form-label']) }}
                                    <select name="local_storage_validation[]" class="select2"
                                        id="local_storage_validation" multiple>
                                        @foreach ($file_type as $f)
                                            <option @if (in_array($f, $local_storage_validations)) selected @endif>
                                                {{ $f }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label"
                                            for="local_storage_max_upload_size">{{ __('Max upload size ( In KB)') }}</label>
                                        <input type="number" name="local_storage_max_upload_size"
                                            class="form-control"
                                            value="{{ !isset($setting['local_storage_max_upload_size']) || is_null($setting['local_storage_max_upload_size']) ? '' : $setting['local_storage_max_upload_size'] }}"
                                            placeholder="{{ __('Max upload size') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="s3-setting row {{ $setting['storage_setting'] == 's3' ? ' ' : 'd-none' }}">

                                <div class=" row ">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label" for="s3_key">{{ __('S3 Key') }}</label>
                                            <input type="text" name="s3_key" class="form-control"
                                                value="{{ !isset($setting['s3_key']) || is_null($setting['s3_key']) ? '' : $setting['s3_key'] }}"
                                                placeholder="{{ __('S3 Key') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label" for="s3_secret">{{ __('S3 Secret') }}</label>
                                            <input type="text" name="s3_secret" class="form-control"
                                                value="{{ !isset($setting['s3_secret']) || is_null($setting['s3_secret']) ? '' : $setting['s3_secret'] }}"
                                                placeholder="{{ __('S3 Secret') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label" for="s3_region">{{ __('S3 Region') }}</label>
                                            <input type="text" name="s3_region" class="form-control"
                                                value="{{ !isset($setting['s3_region']) || is_null($setting['s3_region']) ? '' : $setting['s3_region'] }}"
                                                placeholder="{{ __('S3 Region') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label" for="s3_bucket">{{ __('S3 Bucket') }}</label>
                                            <input type="text" name="s3_bucket" class="form-control"
                                                value="{{ !isset($setting['s3_bucket']) || is_null($setting['s3_bucket']) ? '' : $setting['s3_bucket'] }}"
                                                placeholder="{{ __('S3 Bucket') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label" for="s3_url">{{ __('S3 URL') }}</label>
                                            <input type="text" name="s3_url" class="form-control"
                                                value="{{ !isset($setting['s3_url']) || is_null($setting['s3_url']) ? '' : $setting['s3_url'] }}"
                                                placeholder="{{ __('S3 URL') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="s3_endpoint">{{ __('S3 Endpoint') }}</label>
                                            <input type="text" name="s3_endpoint" class="form-control"
                                                value="{{ !isset($setting['s3_endpoint']) || is_null($setting['s3_endpoint']) ? '' : $setting['s3_endpoint'] }}"
                                                placeholder="{{ __('S3 Endpoint') }}">
                                        </div>
                                    </div>
                                    <div class="form-group col-8 switch-width">
                                        {{ Form::label('s3_storage_validation', __('Only Upload Files'), ['class' => ' form-label']) }}
                                        <select name="s3_storage_validation[]" class="select2"
                                            id="s3_storage_validation" multiple>
                                            @foreach ($file_type as $f)
                                                <option @if (in_array($f, $s3_storage_validations)) selected @endif>
                                                    {{ $f }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="s3_max_upload_size">{{ __('Max upload size ( In KB)') }}</label>
                                            <input type="number" name="s3_max_upload_size" class="form-control"
                                                value="{{ !isset($setting['s3_max_upload_size']) || is_null($setting['s3_max_upload_size']) ? '' : $setting['s3_max_upload_size'] }}"
                                                placeholder="{{ __('Max upload size') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="wasabi-setting row {{ $setting['storage_setting'] == 'wasabi' ? ' ' : 'd-none' }}">
                                <div class=" row ">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label" for="s3_key">{{ __('Wasabi Key') }}</label>
                                            <input type="text" name="wasabi_key" class="form-control"
                                                value="{{ !isset($setting['wasabi_key']) || is_null($setting['wasabi_key']) ? '' : $setting['wasabi_key'] }}"
                                                placeholder="{{ __('Wasabi Key') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="s3_secret">{{ __('Wasabi Secret') }}</label>
                                            <input type="text" name="wasabi_secret" class="form-control"
                                                value="{{ !isset($setting['wasabi_secret']) || is_null($setting['wasabi_secret']) ? '' : $setting['wasabi_secret'] }}"
                                                placeholder="{{ __('Wasabi Secret') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="s3_region">{{ __('Wasabi Region') }}</label>
                                            <input type="text" name="wasabi_region" class="form-control"
                                                value="{{ !isset($setting['wasabi_region']) || is_null($setting['wasabi_region']) ? '' : $setting['wasabi_region'] }}"
                                                placeholder="{{ __('Wasabi Region') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="wasabi_bucket">{{ __('Wasabi Bucket') }}</label>
                                            <input type="text" name="wasabi_bucket" class="form-control"
                                                value="{{ !isset($setting['wasabi_bucket']) || is_null($setting['wasabi_bucket']) ? '' : $setting['wasabi_bucket'] }}"
                                                placeholder="{{ __('Wasabi Bucket') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label" for="wasabi_url">{{ __('Wasabi URL') }}</label>
                                            <input type="text" name="wasabi_url" class="form-control"
                                                value="{{ !isset($setting['wasabi_url']) || is_null($setting['wasabi_url']) ? '' : $setting['wasabi_url'] }}"
                                                placeholder="{{ __('Wasabi URL') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="wasabi_root">{{ __('Wasabi Root') }}</label>
                                            <input type="text" name="wasabi_root" class="form-control"
                                                value="{{ !isset($setting['wasabi_root']) || is_null($setting['wasabi_root']) ? '' : $setting['wasabi_root'] }}"
                                                placeholder="{{ __('Wasabi Root') }}">
                                        </div>
                                    </div>
                                    <div class="form-group col-8 switch-width">
                                        {{ Form::label('wasabi_storage_validation', __('Only Upload Files'), ['class' => 'form-label']) }}

                                        <select name="wasabi_storage_validation[]" class="select2"
                                            id="wasabi_storage_validation" multiple>
                                            @foreach ($file_type as $f)
                                                <option @if (in_array($f, $wasabi_storage_validations)) selected @endif>
                                                    {{ $f }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="wasabi_root">{{ __('Max upload size ( In KB)') }}</label>
                                            <input type="number" name="wasabi_max_upload_size" class="form-control"
                                                value="{{ !isset($setting['wasabi_max_upload_size']) || is_null($setting['wasabi_max_upload_size']) ? '' : $setting['wasabi_max_upload_size'] }}"
                                                placeholder="{{ __('Max upload size') }}">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <input class="btn btn-print-invoice  btn-primary" type="submit"
                            value="{{ __('Save Changes') }}">
                    </div>
                    {{ Form::close() }}
                </div> -->

                {{-- Google calendar --}}
                <!-- <div class="card" id="google-calender">
                    <div class="col-md-12">
                        {{ Form::open(['url' => route('google.calender.settings'), 'enctype' => 'multipart/form-data']) }}
                        <div class="card-header">
                            <div class="row">
                                <div class="col-lg-8 col-md-8 col-sm-8">
                                    <h5 class="">
                                        {{ __('Google Calendar') }}
                                    </h5>
                                </div>

                                <div class="col-lg-4 col-md-4 col-sm-4 text-end">
                                    <div class="col switch-width">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="form-check-input" name="is_enabled"
                                                data-toggle="switchbutton" data-onstyle="primary" id="is_enabled"
                                                {{ isset($settings['is_enabled']) && $settings['is_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                            <label class="custom-control-label form-label" for="is_enabled"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                    {{ Form::label('Google calendar id', __('Google Calendar Id'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('google_clender_id', !empty($settings['google_clender_id']) ? $settings['google_clender_id'] : '', ['class' => 'form-control ', 'placeholder' => __('Google Calendar Id')]) }}
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                    {{ Form::label('Google calendar json file', __('Google Calendar json File'), ['class' => 'col-form-label']) }}
                                    <input type="file" class="form-control" name="google_calender_json_file"
                                        id="file">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn-submit btn btn-primary" type="submit">
                                {{ __('Save Changes') }}
                            </button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div> -->

                {{-- SEO settings --}}
                <!-- <div id="SEO-settings" class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-10 col-md-10 col-sm-10">
                                <h5>{{ __('SEO Settings') }}</h5>
                            </div>

                            @if ($chatgpt == 'on')
                                <div class="col-md-2">
                                    <a href="#" class="btn btn-sm btn-primary" data-size="medium"
                                        data-ajax-popup-over="true" data-url="{{ route('generate', ['seo']) }}"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ __('Generate') }}"
                                        data-title="{{ __('Generate Content With AI') }}">
                                        <i class="fas fa-robot"></i>{{ __(' Generate With AI') }}
                                    </a>
                                </div>
                            @endif

                        </div>
                    </div>
                    <div class="card-body">
                        {{ Form::open(['url' => route('seo.settings'), 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('Meta Keywords', __('Meta Keywords'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('meta_title', !empty($setting['meta_title']) ? $setting['meta_title'] : '', ['class' => 'form-control ', 'placeholder' => __('Meta Keywords')]) }}
                                </div>
                                <div class="form-group">
                                    {{ Form::label('Meta Description', __('Meta Description'), ['class' => 'col-form-label']) }}
                                    {{ Form::textarea('meta_description', !empty($setting['meta_description']) ? $setting['meta_description'] : '', ['class' => 'form-control ', 'placeholder' => __('Meta Description'), 'rows' => 3]) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    {{ Form::label('Meta Image', __('Meta Image'), ['class' => 'col-form-label']) }}
                                </div>
                                <div class="setting-card">
                                    <div class="logo-content">
                                        <img id="image2"
                                            src="{{ $meta_image . '/' . (isset($setting['meta_image']) && !empty($setting['meta_image']) ? $setting['meta_image'] : 'hrmgo.png') }}"
                                            class="img_setting seo_image">
                                    </div>
                                    <div class="choose-files mt-4">
                                        <label for="meta_image">
                                            <div class="bg-primary company_favicon_update"> <i
                                                    class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                            </div>
                                            <input type="file" class="form-control file" id="meta_image"
                                                name="meta_image" data-filename="meta_image">
                                        </label>
                                    </div>
                                    @error('meta_image')
                                        <div class="row">
                                            <span class="invalid-logo" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="card-footer text-end">
                        <input class="btn btn-print-invoice btn-primary" type="submit"
                            value="{{ __('Save Changes') }}">
                    </div>

                    {{ Form::close() }}
                </div> -->

                {{-- Cache settings --}}
                <!-- <div id="cache-settings">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>{{ __('Cache Settings') }}</h5>
                                </div>
                            </div>
                        </div>
                        {{ Form::open(['url' => route('clear.cache')]) }}
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-11 col-md-11 col-sm-11 form-group">
                                    {{ Form::label('Current cache size', __('Current cache size'), ['class' => 'col-form-label']) }}
                                    <div class="input-group mb-5">
                                        <input type="text" class="form-control" value="{{ $file_size }}"
                                            readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text"
                                                id="basic-addon6">{{ __('MB') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn-submit btn btn-primary" type="submit">
                                {{ __('Clear Cache') }}
                            </button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div> -->

                {{-- Webhook Setting --}}
                <!-- <div class="" id="webhook-settings">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">

                            <h5>{{ __('Webhook Settings') }}</h5>
                            <a data-url="{{ route('create.webhook') }}" class="btn btn-sm btn-primary"
                                data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"
                                data-bs-placement="top" data-size="md" data-ajax-popup="true"
                                data-title="{{ __('Create New Webhook') }}">
                                <i class="ti ti-plus"></i>
                            </a>

                        </div>
                        <div class="card-body table-border-style ">
                            <div class="table-responsive">
                                <table class="table" id="pc-dt-simple">
                                    <thead>
                                        <tr>
                                            <th class="w-25">
                                                {{ __('Module') }}</th>
                                            <th class="w-20">
                                                {{ __('URL') }}</th>
                                            <th class="w-30">
                                                {{ __('Method') }}</th>
                                            <th width="150px">
                                                {{ 'Action' }}</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($webhooks as $webhook)
                                            <tr class="Action">
                                                <td class="sorting_1">
                                                    {{ $webhook->module }}</td>
                                                <td class="sorting_3">
                                                    {{ $webhook->url }}</td>
                                                <td class="sorting_2">
                                                    {{ $webhook->method }}</td>
                                                <td class="">
                                                    <div class="dt-buttons">
                                                        <span>
                                                    @can('Edit Webhook')
                                                        <div class="action-btn bg-info me-2">
                                                            <a class="mx-3 btn btn-sm  align-items-center"
                                                                data-url="{{ route('edit.webhook', $webhook->id) }}"
                                                                data-size="md" data-ajax-popup="true"
                                                                data-title="{{ __('Edit Webhook Settings') }}"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-original-title="{{ __('Edit') }}"
                                                                data-bs-placement="top" class="edit-icon"
                                                                data-original-title="{{ __('Edit') }}"><span class="text-white"><i
                                                                    class="ti ti-pencil "></i></span></a>
                                                        </div>
                                                    @endcan
                                                    @can('Delete Webhook')
                                                        <div class="action-btn bg-danger ">
                                                            {!! Form::open([
                                                                'method' => 'DELETE',
                                                                'route' => ['destroy.webhook', $webhook->id],
                                                                'id' => 'delete-form-' . $webhook->id,
                                                            ]) !!}
                                                            <a href="#!" data-bs-toggle="tooltip"
                                                                data-bs-original-title="{{ __('Delete') }}"
                                                                data-bs-placement="top"
                                                                class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                                title="{{ __('Delete') }}">
                                                                <span class="text-white"><i class="ti ti-trash "></i></span></a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                        </span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="text-center">
                                                <td colspan="4">{{ __('No entries found') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div> -->

                {{-- GDPR Cookie Consent --}}
                <!-- <div class="card" id="cookie-settings">
                    {{ Form::model($settings, ['route' => 'cookie.setting', 'method' => 'post']) }}
                    <div
                        class="card-header flex-column flex-lg-row  d-flex align-items-lg-center gap-2 justify-content-between">
                        <h5>{{ __('Cookie Settings') }}</h5>
                        <div class="d-flex align-items-center">
                            {{ Form::label('enable_cookie', __('Enable cookie'), ['class' => 'col-form-label p-0 fw-bold me-3']) }}
                            <div class="custom-control custom-switch" onclick="enablecookie()">
                                <input type="checkbox" data-toggle="switchbutton" data-onstyle="primary"
                                    name="enable_cookie" class="form-check-input input-primary " id="enable_cookie"
                                    {{ $settings['enable_cookie'] == 'on' ? ' checked ' : '' }}>
                                <label class="custom-control-label mb-1" for="enable_cookie"></label>
                            </div>
                        </div>
                    </div>
                    <div class="card-body cookieDiv {{ $settings['enable_cookie'] == 'off' ? 'disabledCookie ' : '' }}">
                        <div class="row ">

                            @if ($chatgpt == 'on')
                                <div class="text-end">
                                    <div class="mt-0">
                                        <a data-size="md" class="btn btn-primary text-white btn-sm"
                                            data-ajax-popup-over="true" data-url="{{ route('generate', ['cookie']) }}"
                                            data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
                                            <i class="fas fa-robot"></i> <span>{{ __('Generate with AI') }}</span>
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-6">
                                <div class="form-check form-switch custom-switch-v1" id="cookie_log">
                                    <input type="checkbox" name="cookie_logging"
                                        class="form-check-input input-primary cookie_setting" id="cookie_logging"
                                        onclick="enableButton()"
                                        {{ $settings['cookie_logging'] == 'on' ? ' checked ' : '' }}>
                                    <label class="form-check-label"
                                        for="cookie_logging">{{ __('Enable logging') }}</label>
                                </div>
                                <div class="form-group">
                                    {{ Form::label('cookie_title', __('Cookie Title'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('cookie_title', null, ['class' => 'form-control cookie_setting', 'placeholder' => __('Cookie Title')]) }}
                                </div>
                                <div class="form-group ">
                                    {{ Form::label('cookie_description', __('Cookie Description'), ['class' => ' form-label']) }}
                                    {!! Form::textarea('cookie_description', null, [
                                        'class' => 'form-control cookie_setting',
                                        'rows' => '3',
                                        'placeholder' => __('Cookie Description'),
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch custom-switch-v1 ">
                                    <input type="checkbox" name="necessary_cookies"
                                        class="form-check-input input-primary" id="necessary_cookies" checked
                                        onclick="return false">
                                    <label class="form-check-label"
                                        for="necessary_cookies">{{ __('Strictly necessary cookies') }}</label>
                                </div>
                                <div class="form-group ">
                                    {{ Form::label('strictly_cookie_title', __(' Strictly Cookie Title'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('strictly_cookie_title', null, ['class' => 'form-control cookie_setting', 'placeholder' => __('Strictly Cookie Title')]) }}
                                </div>
                                <div class="form-group ">
                                    {{ Form::label('strictly_cookie_description', __('Strictly Cookie Description'), ['class' => ' form-label']) }}
                                    {!! Form::textarea('strictly_cookie_description', null, [
                                        'class' => 'form-control cookie_setting ',
                                        'rows' => '3',
                                        'placeholder' => __('Strictly Cookie Description'),
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-12">
                                <h5>{{ __('More Information') }}</h5>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group ">
                                    {{ Form::label('more_information_description', __('Contact Us Description'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('more_information_description', null, ['class' => 'form-control cookie_setting', 'placeholder' => __('Contact Us Description')]) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group ">
                                    {{ Form::label('contactus_url', __('Contact Us URL'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('contactus_url', null, ['class' => 'form-control cookie_setting', 'placeholder' => __('Contact Us URL')]) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div id="csv_file" class="col-md-6">
                                @if (isset($settings['cookie_logging']) && $settings['cookie_logging'] == 'on')
                                    <label for="file"
                                        class="form-label">{{ __('Download cookie accepted data') }}</label>
                                    <a href="{{ asset(Storage::url('uploads/sample')) . '/data.csv' }}"
                                        class="btn btn-primary mr-2 ">
                                        <i class="ti ti-download"></i>
                                    </a>
                                @endif
                            </div>

                            <div class="text-end col-md-6">
                                <input type="submit" class="btn btn-xs btn-primary"
                                    value="{{ __('Save Changes') }}">
                            </div>
                        </div>
                    </div>

                    {{ Form::close() }}
                </div> -->

                {{-- ChatGPT Key Settings --}}
                <!-- <div class="card" id="pills-chatgpt-settings">
                    <div class="col-md-12">
                        {{ Form::model($settings, ['route' => 'settings.chatgptkey', 'method' => 'post']) }}
                        <div class="card-header">
                            <div class="row">
                                <div class="col-lg-8 col-md-8 col-sm-8">
                                    <h5 class="">
                                        {{ __('Chat GPT Key Settings') }}
                                    </h5>
                                    <small>{{ __('Edit your key details') }}</small>
                                </div>

                                <div class="col-lg-4 col-md-4 col-sm-4 text-end">
                                    <div class="col switch-width">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="form-check-input" name="enable_chatgpt"
                                                data-toggle="switchbutton" data-onstyle="primary" id="enable_chatgpt"
                                                {{ isset($settings['enable_chatgpt']) && $settings['enable_chatgpt'] == 'on' ? 'checked="checked"' : '' }}>
                                            <label class="custom-control-label form-label" for="enable_chatgpt"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {{ Form::label('Chat GPT Key', __('Chat GPT Key'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('chatgpt_key', isset($settings['chatgpt_key']) ? $settings['chatgpt_key'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Chatgpt Key Here')]) }}
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('Chat GPT Model', __('Chat GPT Model'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('chatgpt_model', isset($settings['chatgpt_model']) ? $settings['chatgpt_model'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Chatgpt Model Here')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn-submit btn btn-primary" type="submit">
                                {{ __('Save Changes') }}
                            </button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div> -->

                {{-- remove biometric code --}}
                {{-- Biometric Attendance Seetings --}}
                {{-- <div class="card" id="biometric-attendance">
                    <div class="col-md-12">
                        {{ Form::open(['route' => ['biometric-settings.store'], 'method' => 'post']) }}
                        <div class="card-header">
                            <div class="row">
                                <div class="col-lg-8 col-md-8 col-sm-8">
                                    <h5 class="">
                                        {{ __('Biometric Attendance') }}
                                    </h5>
                                    <small class="text-muted">
                                        <b class="text-danger">{{ __('Note') }}: </b>
                                        {{ __('Note that you can use the biometric attendance system only if you are using the ZKTeco machine for biometric attendance.') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    {{ Form::label('zkteco_api_url', __('ZKTeco Api URL'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('zkteco_api_url', !empty($settings['zkteco_api_url']) ? $settings['zkteco_api_url'] : '', ['class' => 'form-control ', 'placeholder' => 'ZKTeco Api URL', 'required' => 'required']) }}
                                    <small>
                                        <b class="text-dark">{{ __('Example:') }}</b> http://110.78.645.123:8080
                                    </small>
                                </div>
                                <div class="col-md-3 form-group">
                                    {{ Form::label('username', __('Username'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('username', !empty($settings['username']) ? $settings['username'] : '', ['class' => 'form-control ', 'placeholder' => 'Username', 'required' => 'required']) }}
                                </div>
                                <div class="col-md-3 form-group">
                                    {{ Form::label('user_password', __('Password'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('user_password', !empty($settings['user_password']) ? $settings['user_password'] : '', ['class' => 'form-control ', 'placeholder' => 'Password', 'required' => 'required']) }}
                                </div>
                                <div class="col-md-8 form-group">
                                    {{ Form::label('auth_token', __('Auth Token'), ['class' => 'form-label']) }}
                                    @if (empty($settings['auth_token']))
                                        <small class="text-danger">
                                            {{ __('Please first generate auth token.') }}
                                        </small>
                                    @endif
                                    {{ Form::textarea('', !empty($settings['auth_token']) ? $settings['auth_token'] : null, ['class' => 'form-control font-style', 'disabled' => 'disabled', 'rows' => 3]) }}
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn-submit btn btn-primary" type="submit">
                                {{ __('Generate Token') }}
                            </button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div> --}}

            </div>
        </div>
    @endsection
