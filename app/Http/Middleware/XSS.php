<?php

namespace App\Http\Middleware;

use App\Models\LandingPageSection;
use App\Models\Utility;
use App\Models\GenerateOfferLetter;
use Illuminate\Support\Facades\Config;
use Closure;
use Illuminate\Support\Facades\Schema;

class XSS
{
    use \RachidLaasri\LaravelInstaller\Helpers\MigrationsHelper;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if (\Auth::check()) {
                try {
                    $settings = Utility::settings();
                    if (!empty($settings['timezone'])) {
                        Config::set('app.timezone', $settings['timezone']);
                        date_default_timezone_set(Config::get('app.timezone', 'UTC'));
                    }
                } catch (\Exception $e) {
                    \Log::warning('XSS middleware: Error loading settings: ' . $e->getMessage());
                }

                try {
                    $user = \Auth::user();
                    if ($user && isset($user->lang)) {
                        \App::setLocale($user->lang);
                    }
                } catch (\Exception $e) {
                    \Log::warning('XSS middleware: Error setting locale: ' . $e->getMessage());
                }

                if (\Auth::user()->type == 'company') {
                    try {
                        if (Schema::hasTable('ch_messages')) {
                            if (Schema::hasColumn('ch_messages', 'type') == false) {
                                Schema::drop('ch_messages');
                                \DB::table('migrations')->where('migration', 'like', '%ch_messages%')->delete();
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::warning('XSS middleware: Error checking ch_messages table: ' . $e->getMessage());
                    }

                    try {
                        $migrations = $this->getMigrations();
                        $messengerMigration = Utility::get_messenger_packages_migration();
                        $dbMigrations = $this->getExecutedMigrations();
                        $Modulemigrations = glob(base_path() . '/Modules/LandingPage/Database' . DIRECTORY_SEPARATOR . 'Migrations' . DIRECTORY_SEPARATOR . '*.php');
                        $numberOfUpdatesPending = (count($migrations) + count($Modulemigrations) + $messengerMigration) - count($dbMigrations);

                        if ($numberOfUpdatesPending > 0) {
                            Utility::addNewData();
                            return redirect()->route('LaravelUpdater::welcome');
                        }
                    } catch (\Exception $e) {
                        \Log::warning('XSS middleware: Error checking migrations: ' . $e->getMessage());
                        // Continue with request even if migration check fails
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('XSS middleware fatal error: ' . $e->getMessage());
            // Don't break the request, continue
        }

        return $next($request);
    }
}
