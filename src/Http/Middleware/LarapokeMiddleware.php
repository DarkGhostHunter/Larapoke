<?php

namespace DarkGhostHunter\Larapoke\Http\Middleware;

use Closure;

class LarapokeMiddleware
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

        // Don't evaluate the response under "auto" or "blade" modes.
        if (app('config')->get('larapoke.mode') === 'middleware') {
            $response = $this->shouldInjectScript($response, $detect);
        }

        return $response;
    }

    /**
     * Should inject the script into the response.
     *
     * @param $response
     * @param string|null $detect
     * @return \Illuminate\Http\Response
     */
    public function shouldInjectScript($response, $detect)
    {
        if (($detect === 'detect' && $this->hasCsrf($response))
            || $detect !== 'detect') {
            return $this->injectScript($response);
        }

        return $response;
    }
}