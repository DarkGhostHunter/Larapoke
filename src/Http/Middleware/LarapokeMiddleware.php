<?php

namespace DarkGhostHunter\Larapoke\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Config\Repository;

class LarapokeMiddleware
{
    use InjectsLarapokeScript;

    /**
     * The Config Repository for this Laravel application
     *
     * @var bool
     */
    protected $modeIsMiddleware = false;

    /**
     * LarapokeGlobalMiddleware constructor.
     *
     * @param  Repository  $config
     */
    public function __construct(Repository $config)
    {
        $this->modeIsMiddleware = $config->get('larapoke.mode') === 'middleware';
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $detect
     * @return mixed
     */
    public function handle($request, Closure $next, string $detect = null)
    {
        $response = $next($request);

        // Don't evaluate the response under "auto" or "blade" modes.
        if ($this->shouldInjectScript($request, $response, $detect)) {
            return $this->injectScript($response);
        }

        return $response;
    }

    /**
     * Determine if we should inject the script into the response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response  $response
     * @param  string|null  $detect
     * @return bool
     */
    public function shouldInjectScript(Request $request, $response, $detect)
    {
        if (! $this->modeIsMiddleware) {
            return false;
        }

        // Check first if the middleware has to detect if there is a CSRF token
        // before injecting the script in the response. When not detecting,
        // then we tell to inject the script anyway into the Response.
        $injectAnyway = $detect !== 'detect';

        return $injectAnyway || $this->isInjectable($request, $response);
    }
}