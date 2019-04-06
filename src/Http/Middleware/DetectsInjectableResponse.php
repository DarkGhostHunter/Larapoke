<?php

namespace DarkGhostHunter\Larapoke\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

trait DetectsInjectableResponse
{
    /**
     * Detect if the Request accepts HTML and is not an AJAX/PJAX Request
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response | \Illuminate\Http\JsonResponse $response
     * @return bool
     */
    protected function isHtml(Request $request, $response)
    {
        return $response instanceof Response
            && $request->acceptsHtml()
            && ! $request->ajax()
            && ! $request->pjax()
            && ! $request->exception;
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

        // We don't know how the dev will code its application HTML, he could even
        // use all-caps lock everything. We don't enforce a code-style, we will
        // use "stripos" instead of "strpos" for a tiny 6% less performance.
        $hasCsrfHeader = stripos($content, 'name="csrf-token"');
        $hasCsrfInput = stripos($content, 'name="_token"');

        return $hasCsrfHeader || $hasCsrfInput;
    }
}