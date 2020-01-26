<?php

namespace Tests;

trait RegistersPackages
{
    protected function getPackageProviders($app)
    {
        return [
            'DarkGhostHunter\Larapoke\LarapokeServiceProvider',
            'Laravel\Ui\UiServiceProvider' // Needed for auth scaffolding
        ];
    }
}