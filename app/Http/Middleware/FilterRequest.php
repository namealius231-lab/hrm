<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FilterRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $input = $request->all();
            array_walk_recursive($input, function (&$value) {
                if (is_string($value)) {
                    try {
                        $value = htmlspecialchars_decode($value);
                        $value = preg_replace('/<\s*script\b[^>]*>(.*?)<\s*\/\s*script\s*>/is', '', $value);
                        $value = str_replace(['&lt;', '&gt;', 'javascript', 'script', 'alert'], '', $value);
                    } catch (\Exception $e) {
                        // If filtering fails, leave value as is
                        \Log::warning('FilterRequest: Error filtering value: ' . $e->getMessage());
                    }
                }
            });
            $request->merge($input);
        } catch (\Exception $e) {
            \Log::error('FilterRequest middleware error: ' . $e->getMessage());
            // Continue with request even if filtering fails
        }
        
        return $next($request);
    }
}
