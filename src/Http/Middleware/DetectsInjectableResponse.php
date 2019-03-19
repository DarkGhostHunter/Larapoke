<?php

namespace DarkGhostHunter\Larapoke\Http\Middleware;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait DetectsInjectableResponse
{
    /**
     * Detect if the Response has form or CSRF Token
     *
     * @param \Illuminate\Http\Response|\Illuminate\Http\JsonResponse $response
     * @return bool
     */
    protected function hasCsrf($response)
    {
        $content = $response->content();

        // We don't know how the dev will code its application HTML, he could even
        // use all-caps lock everything. We don't enforce a code-style, we will
        // use "stripos" instead of "strpos" for a tiny 6% less performance.
        $hasCsrfHeader = stripos($content, 'name="csrf-token"');
        $hasCsrfInput = stripos($content, 'name="_token"');

        return $hasCsrfHeader || $hasCsrfInput;
    }

    /**
     * Detect if the Request accepts HTML and is not an AJAX/PJAX Request
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response | \Illuminate\Http\JsonResponse $response
     * @return bool
     */
    protected function isHtml(Request $request, $response)
    {
        return !$response instanceof JsonResponse && $request->acceptsHtml() && !$request->ajax() && !$request->pjax();
    }
}