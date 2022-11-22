<?php

namespace App\Http\Middleware;

use Closure;

class DisableOnProduction
{
    /**
     * Create a new middleware instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->environment = app()->environment();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($this->environment === 'production') {
            return response()->json([
                'code' => (int) 403,
                'message' => 'This function is disabled when environment is on production',
            ], 403);
        }

        return $next($request);
    }
}
