<?php

namespace DarkGhostHunter\Larapoke\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Routing\Controller;

class LarapokeController extends Controller
{
    /**
     * Return an empty Ok response to the Poke script.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __invoke()
    {
        return app(ResponseFactory::class)->make()->setStatusCode(204);
    }

}