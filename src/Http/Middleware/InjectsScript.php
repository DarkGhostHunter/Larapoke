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
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function injectScript($response)
    {
        // To inject the script automatically, we will do it before the ending
        // body tag. If it's not found, the response may not be valid HTML,
        // so we will bail out returning the original untouched content.
        if (!$endBodyPosition = stripos($content = $response->content(), '</body>')) {
            return $response;
        };

        // Calling Build instead of instancing the class will allow to automatically
        // inject the services into the directive instead of manually doing it,
        // since we don't know what implementation the application may have.
        $script = app()->build(LarapokeDirective::class)->getRenderedScript();

        return $response->setContent(
            substr_replace($content, $script, $endBodyPosition, 0)
        );
    }
}