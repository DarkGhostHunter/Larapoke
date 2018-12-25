<?php

namespace DarkGhostHunter\Larapoke\Http\Middleware;

use Illuminate\Http\Response;

trait DetectsCsrf
{
    /**
     * Detect if the Response has form or CSRF Token
     *
     * @param $response
     * @return bool
     */
    protected function hasCsrf(Response $response)
    {
        if (!$response->isOk() && $this->isHtml($response)) {
            return false;
        }

        $content = $response->content();

        $hasCsrfHeader = stripos($content, 'name="csrf-token"');
        $hasCsrfInput = stripos($content, 'name="_token"');

        return $hasCsrfHeader || $hasCsrfInput;
    }

    /**
     * Detect if the Response is HTML by its "content-type" header
     *
     * @param Response $response
     * @return bool
     */
    protected function isHtml(Response $response)
    {
        return strpos($response->headers->get('content-type'), 'text/html') === false;
    }
}