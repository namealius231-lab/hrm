<?php

namespace App\Http\Controllers\Auth;

use App\Events\VerifyReCaptchaToken;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Utility;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;
use App\Models\GenerateOfferLetter;
use App\Models\JoiningLetter;
use App\Models\ExperienceCertificate;
use App\Models\NOC;
use Illuminate\Support\Facades\Validator;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */

    public function __construct()
    {
        // Middleware is already applied in routes/auth.php
    }

    public function create($lang = '')
    {
        try {
            if ($lang == '') {
                $lang = \App\Models\Utility::getValByName('default_language');
                if (empty($lang)) {
                    $lang = 'en';
                }
            }
        } catch (\Exception $e) {
            // If database access fails, default to English
            $lang = 'en';
        }

        // Ensure lang is not empty
        if (empty($lang)) {
            $lang = 'en';
        }

        \App::setLocale($lang);
        return view('auth.register', compact('lang'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $settings = Utility::settings();
        if (isset($settings['recaptcha_module']) && $settings['recaptcha_module'] == 'yes') {
            if ($settings['google_recaptcha_version'] == 'v2-checkbox') {
                $validation['g-recaptcha-response'] = 'required';
            } elseif ($settings['google_recaptcha_version'] == 'v3') {
                $result = event(new VerifyReCaptchaToken($request));

                if (!isset($result[0]['status']) || $result[0]['status'] != true) {
                    $key = 'g-recaptcha-response';
                    $request->merge([$key => null]); // Set the key to null

                    $validation['g-recaptcha-response'] = 'required';
                }
            } else {
                $validation = [];
            }
        } else {
            $validation = [];
        }

        $validator = Validator::make(
            $request->all(),
            $validation
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $default_language = \DB::table('settings')->select('value')->where('name', 'default_language')->first();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => 'company',
            'lang' => !empty($default_language) ? $default_language->value : '',
            'plan' => 1,
            'created_by' => 1,
        ]);


        $role_r = Role::findByName('company');

        $user->assignRole($role_r);
        $user->userDefaultDataRegister($user->id);
        GenerateOfferLetter::defaultOfferLetterRegister($user->id);
        ExperienceCertificate::defaultExpCertificatRegister($user->id);
        JoiningLetter::defaultJoiningLetterRegister($user->id);
        NOC::defaultNocCertificateRegister($user->id);



        // event(new Registered($user));

        Auth::login($user);

        try {

            event(new Registered($user));
            $role_r = Role::findByName('company');
            $user->userDefaultData();
            $user->assignRole($role_r);
        } catch (\Exception $e) {

            $user->delete();

            return redirect()->route('register', ['lang' => 'en'])->with('status', __('Email SMTP settings not configured so please contact your site admin.'));
        }

        return view('auth.verify-email');

        //return redirect(RouteServiceProvider::HOME);

    }

    public function showRegistrationForm($lang = '')
    {
        return redirect()->route('login');
        if (empty($lang)) {
            $lang = Utility::getValByName('default_language');
        }

        \App::setLocale($lang);
        if (Utility::getValByName('disable_signup_button') == 'on') {
            return view('auth.register', compact('lang'));
        } else {
            return abort('404', 'Page not found');
        }
    }
}
