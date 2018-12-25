<?php

namespace DarkGhostHunter\Larapoke\Http\Middleware;

use Illuminate\Http\Response;

trait DetectsCsrf
{
    /**
     * Detect if the Response is OK and has form or CSRF Token
     *
     * @param $response
     * @return bool
     */
    protected function hasCsrf(Response $response)
    {
        if (!$response->isOk() && strpos($response->headers->get('content-type'), 'text/html') === false) {
            return false;
        }

        $content = $response->content();

        $hasCsrfHeader = stripos($content, 'name="csrf-token"');
        $hasCsrfInput = stripos($content, 'name="_token"');

        return $hasCsrfHeader || $hasCsrfInput;
    }
}