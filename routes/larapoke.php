<?php

// We will use our own RouteGenerator class to handle the registration of
// Larapoke routes into the application, instead of writing the code in
// this file. Also, it allows us to test the route generation easily.
app()->make(\DarkGhostHunter\Larapoke\Http\RouteGenerator::class)->setRoutes();