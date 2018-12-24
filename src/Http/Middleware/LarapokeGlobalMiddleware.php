<?php

namespace DarkGhostHunter\Larapoke\Http\Middleware;

use Closure;

class LarapokeGlobalMiddleware
{
    use DetectsCsrf, InjectsScript;

    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param bool $detect
     * @return mixed
     */
    public function handle($request, Closure $next, $detect = null)
    {
        $response = $next($request);

        if ($this->hasCsrf($response)) {
            $this->injectScript($response);
        }

        return $response;
    }
}