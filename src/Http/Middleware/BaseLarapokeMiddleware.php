<?php

namespace DarkGhostHunter\Larapoke\Http\Middleware;

abstract class BaseLarapokeMiddleware
{
    use DetectsInjectableResponse, InjectsScript;
}