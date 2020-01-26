<?php

namespace DarkGhostHunter\Larapoke\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

trait DetectsInjectableResponse
{
    /**
     * Determines if the response can be injected with Larapoke script.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response | \Illuminate\Http\JsonResponse  $response
     * @return bool
     */
    protected function isInjectable(Request $request, $response) {
        return $response->isSuccessful()
            && $this->isRenderableResponse($response)
            && $this->wantsFullPage($request)
            && $this->hasCsrf($response);
    }

    /**
     * Detect if the Request accepts HTML and is not an AJAX/PJAX Request
     *
     * @param  \Illuminate\Http\Response | \Illuminate\Http\JsonResponse  $response
     * @return bool
     */
    protected function isRenderableResponse($response)
    {
        return $response instanceof Response;
    }

    /**
     * Return if the Request wants a full page instead of a part (AJAX requests)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function wantsFullPage(Request $request)
    {
        return  $request->acceptsHtml() && ! $request->ajax() && ! $request->pjax();
    }

    /**
     * Detect if the Response has form or CSRF Token
     *
     * @param \Illuminate\Http\Response $response
     * @return bool
     */
    protected function hasCsrf(Response $response)
    {
        $content = $response->content();

        return strpos($content, 'name="csrf-token"') || strpos($content, 'name="_token"');
    }
}