<?php

namespace App\Http\Middleware;

use App\Models\Utility;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class getPusherSettings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if not installed
        if (!file_exists(storage_path() . "/installed")) {
            return $next($request);
        }

        try {
            // Check if settings table exists - if not, skip middleware
            if (!Schema::hasTable('settings')) {
                return $next($request);
            }

            // Try to get settings - if fails or returns null, skip
            $settings = Utility::settings();
            
            // If settings is null or empty, skip configuration
            if (empty($settings) || !is_array($settings)) {
                return $next($request);
            }

            // Only configure pusher if settings exist and are not empty
            $pusherKey = isset($settings['pusher_app_key']) && !empty($settings['pusher_app_key']) ? $settings['pusher_app_key'] : '';
            $pusherSecret = isset($settings['pusher_app_secret']) && !empty($settings['pusher_app_secret']) ? $settings['pusher_app_secret'] : '';
            $pusherAppId = isset($settings['pusher_app_id']) && !empty($settings['pusher_app_id']) ? $settings['pusher_app_id'] : '';
            $pusherCluster = isset($settings['pusher_app_cluster']) && !empty($settings['pusher_app_cluster']) ? $settings['pusher_app_cluster'] : '';

            // Only set config if at least one value is present
            if (!empty($pusherKey) || !empty($pusherSecret) || !empty($pusherAppId) || !empty($pusherCluster)) {
                config([
                    'chatify.pusher.key' => $pusherKey,
                    'chatify.pusher.secret' => $pusherSecret,
                    'chatify.pusher.app_id' => $pusherAppId,
                    'chatify.pusher.options.cluster' => $pusherCluster,
                ]);
            }
        } catch (\Exception $e) {
            // If any error occurs (DB connection, table issues, etc.), skip middleware
            // Log error in development but don't break the request
            if (config('app.debug')) {
                \Log::warning('Pusher settings middleware error: ' . $e->getMessage());
            }
        }

        return $next($request);
    }
}
