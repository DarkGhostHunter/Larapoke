<?php

namespace DarkGhostHunter\Larapoke\Http\Middleware;

use DarkGhostHunter\Larapoke\Blade\LarapokeDirective;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

trait InjectsLarapokeScript
{
    /**
     * Determines if the response can be injected with Larapoke script.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response | \Illuminate\Http\JsonResponse  $response
     * @return bool
     */
    protected function isInjectable(Request $request, $response): bool
    {
        return $response->isSuccessful()
            && $this->isNormalResponse($response)
            && $this->wantsFullHtml($request)
            && $this->hasCsrf($response);
    }

    /**
     * Detect if the Response is normal.
     *
     * @param  \Illuminate\Http\Response | \Illuminate\Http\JsonResponse  $response
     * @return bool
     */
    protected function isNormalResponse($response)
    {
        return $response instanceof Response;
    }

    /**
     * Return if the Request wants a full page instead of a part (AJAX requests).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function wantsFullHtml(Request $request)
    {
        return $request->acceptsHtml() && ! $request->ajax() && ! $request->pjax();
    }

    /**
     * Detect if the Response has form or CSRF Token.
     *
     * @param  \Illuminate\Http\Response  $response
     * @return bool
     */
    protected function hasCsrf(Response $response)
    {
        $content = $response->content();

        return strpos($content, 'name="csrf-token"') || strpos($content, 'name="_token"');
    }

    /**
     * Sets the Script in the body
     *
     * @param  \Illuminate\Http\Response  $response
     * @return \Illuminate\Http\Response
     */
    protected function injectScript($response)
    {
        // To inject the script automatically, we will do it before the ending
        // body tag. If it's not found, the response may not be valid HTML,
        // so we will bail out returning the original untouched content.
        if (! $endBodyPosition = stripos($content = $response->content(), '</body>')) {
            return $response;
        }

        return $response->setContent(
            substr_replace(
                $content, app(LarapokeDirective::class)->getRenderedScript(), $endBodyPosition, 0
            )
        );
    }
}