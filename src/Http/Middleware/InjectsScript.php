<?php

namespace DarkGhostHunter\Larapoke\Http\Middleware;

use DarkGhostHunter\Larapoke\Blade\LarapokeDirective;
use Illuminate\Http\Response;

trait InjectsScript
{
    /**
     * Sets the Script in the body
     *
     * @param Response $response
     * @return Response
     */
    protected function injectScript($response)
    {
        $content = $response->content();

        if ($endBodyPosition = stripos($content, '</body>')) {

            $script = (new LarapokeDirective(app('config'), app('view')))();

            $response->setContent(
                substr_replace($content, $script, $endBodyPosition, 0)
            );
        };

        return $response;
    }
}