<?php

namespace DarkGhostHunter\Larapoke\Http\Middleware;

use Illuminate\Http\Response;

trait DetectsCsrf
{
    /**
     * Detect if the Response has a Form and
     *
     * @param $response
     * @return bool
     */
    protected function hasCsrf(Response $response)
    {
        $isHtml = strpos($response->headers->get('content-type'), 'text/html') !== false;

        if ($response->isOk() && $isHtml) {

            $content = $response->content();

            $hasCsrfHeader = stripos($content, 'name="csrf-token"');
            $hasCsrfInput = stripos($content, 'name="_token"');

            return $hasCsrfHeader || $hasCsrfInput;
        }

        return false;
    }
}