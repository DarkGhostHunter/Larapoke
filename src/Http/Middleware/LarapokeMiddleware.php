<?php

namespace DarkGhostHunter\Larapoke\Http\Middleware;

use Closure;
use DarkGhostHunter\Larapoke\Blade\LarapokeDirective;
use Illuminate\Http\Response;

class LarapokeMiddleware
{
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
        return $this->shouldInjectScript($next($request), $detect);
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
        $mode = app('config')->get('larapoke.mode');

        // Disable any injection if we are using blade templates
        if ($mode !== 'blade') {

            // On Auto or manual middleware using detection, check if there is a
            // form to keep alive. Otherwise we will inject the script inside
            // the Response as long the detection flag was not passed down.
            if ($mode === 'auto' || $detect === 'detect') {
                $this->hasCsrf($response) ?: $this->setScriptInContent($response);
            } elseif ($detect !== 'detect') {
                $this->setScriptInContent($response);
            }

        }

        return $response;
    }

    /**
     * Detect if the Response has a Form and
     *
     * @param $response
     * @return bool
     */
    protected function hasCsrf(Response $response)
    {
        if ($response->isOk()) {

            $content = $response->content();

            $hasCsrfHeader = stripos($content, 'name="csrf-token"');
            $hasCsrfInput = stripos($content, 'name="_token"');

            return $hasCsrfHeader || $hasCsrfInput;
        }
        return false;
    }

    /**
     * Sets the Script in the body
     *
     * @param Response $response
     */
    protected function setScriptInContent(Response $response)
    {
        $content = $response->content();

        $script = (new LarapokeDirective(app('config'), app('view')))();

        $endBodyPosition = stripos($content, '</body>');

        $response->setContent(
            substr_replace($content, $script, $endBodyPosition, 0)
        );
    }
}