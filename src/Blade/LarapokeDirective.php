<?php

namespace DarkGhostHunter\Larapoke\Blade;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\View\Factory;

class LarapokeDirective
{

    /**
     * The Config for the Blade Directive
     *
     * @var array
     */
    protected static $config;

    /**
     * The View Factory Instance
     *
     * @var Factory
     */
    protected static $view;

    /**
     * LarapokeDirective constructor.
     *
     * We use static variables son each Larapoke call doesn't get everything all again.
     *
     * @param Repository $config
     * @param Factory $view
     */
    public function __construct(Repository $config, Factory $view)
    {
        self::$view = self::$view ?? $view;
        self::$config = self::$config ?? $this->parseConfig($config);
    }

    /**
     * Parse de Config and save it
     *
     * @param Repository $config
     * @return array
     */
    protected function parseConfig(Repository $config)
    {
        $session = $config->get('session.lifetime') * 60;

        return [
            'route' => $config->get('larapoke.poking.route'),
            'interval' => $session / $config->get('larapoke.times'),
            'timeout' => $config->get('larapoke.timeout'),
            'lifetime' => $session,
        ];
    }

    /**
     * Return the Directive
     *
     * @return string
     */
    public function __invoke()
    {
        return self::$view->make('larapoke::script', self::$config)->render();
    }
}