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
    protected function injectScript(Response $response)
    {
        $content = $response->content();

        $script = (new LarapokeDirective(app('config'), app('view')))();

        $endBodyPosition = stripos($content, '</body>');

        $response->setContent(
            substr_replace($content, $script, $endBodyPosition, 0)
        );

        return $response;
    }
}