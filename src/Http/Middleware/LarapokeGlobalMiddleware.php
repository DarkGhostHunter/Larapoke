<?php

namespace DarkGhostHunter\Larapoke\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LarapokeGlobalMiddleware
{
    use InjectsLarapokeScript;

    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request|\Illuminate\Foundation\Http\FormRequest $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($request instanceof Request && $this->isInjectable($request, $response)) {
            $this->injectScript($response);
        }

        return $response;
    }
}