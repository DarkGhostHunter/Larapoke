<?php

namespace DarkGhostHunter\Larapoke\Http\Middleware;

use Closure;

class LarapokeGlobalMiddleware
{
    use DetectsInjectableResponse, InjectsScript;

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

        if ($response->isOk() && $this->isHtml($response) && $this->hasCsrf($response)) {
            $this->injectScript($response);
        }

        return $response;
    }
}