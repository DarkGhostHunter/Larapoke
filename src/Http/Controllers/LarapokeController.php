<?php

namespace DarkGhostHunter\Larapoke\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Routing\Controller;

class LarapokeController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        return app(ResponseFactory::class)->make()->setStatusCode(204);
    }

}