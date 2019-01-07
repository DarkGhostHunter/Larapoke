<?php

namespace DarkGhostHunter\Larapoke\Http\Middleware;

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

        $hasCsrfHeader = stripos($content, 'name="csrf-token"');
        $hasCsrfInput = stripos($content, 'name="_token"');

        return $hasCsrfHeader || $hasCsrfInput;
    }

    /**
     * Detect if the Response is HTML by its "content-type" header
     *
     * @param \Illuminate\Http\Response|\Illuminate\Http\JsonResponse $response
     * @return bool
     */
    protected function isHtml($response)
    {
        return strpos($response->headers->get('Content-type'), 'text/html') !== false;
    }
}