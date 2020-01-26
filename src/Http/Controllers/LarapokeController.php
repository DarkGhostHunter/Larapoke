<?php

namespace DarkGhostHunter\Larapoke\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class LarapokeController extends Controller
{
    /**
     * Return an empty Ok response to the Poke script.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        return new Response('', Response::HTTP_NO_CONTENT);
    }
}