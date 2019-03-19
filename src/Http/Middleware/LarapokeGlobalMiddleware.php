<?php

namespace DarkGhostHunter\Larapoke\Http\Middleware;

use Closure;

class LarapokeGlobalMiddleware extends BaseLarapokeMiddleware
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($response->isOk() && $this->isHtml($request, $response) && $this->hasCsrf($response)) {
            $this->injectScript($response);
        }

        return $response;
    }
}