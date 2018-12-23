<?php

$config = $this->app['config'];

$this->app['router']
    ->name($config->get('larapoke.poking.name', 'larapoke'))
    ->domain($config->get('larapoke.poking.domain'))
    ->match('head', $config->get('larapoke.poking.route', 'poke'))
    ->middleware($config->get('larapoke.poking.middleware'))
    ->uses('DarkGhostHunter\Larapoke\Http\Controllers\LarapokeController');