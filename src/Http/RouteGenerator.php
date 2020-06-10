<?php

namespace DarkGhostHunter\Larapoke\Http;

use Illuminate\Support\Arr;
use Illuminate\Routing\Router;
use Illuminate\Contracts\Config\Repository as Config;
use DarkGhostHunter\Larapoke\Http\Controllers\LarapokeController;

class RouteGenerator
{
    /**
     * Configuration
     *
     * @var Config
     */
    protected $config;

    /**
     * Application instance
     *
     * @var Router
     */
    protected $router;

    /**
     * GenerateRoutes constructor.
     *
     * @param Router $router
     * @param Config $config
     */
    public function __construct(Router $router, Config $config)
    {
        $this->config = $config;
        $this->router = $router;
    }

    /**
     * Parses the configuration from Larapoke
     *
     * @return array
     */
    protected function parseConfig()
    {
        $configs = array_flip([
            'route',
            'name',
            'domain',
            'middleware',
        ]);

        foreach ($configs as $key => &$config) {
            $config = $this->config->get('larapoke.poking.'.$key);
        }

        return $configs;
    }

    /**
     * Automatically registers routes
     *
     * @return void
     */
    public function setRoutes()
    {
        $config = $this->parseConfig();

        // When the "domain" config is null, we will just register a global route
        // that will respond to all domains. Otherwise, we will wrap the value
        // and traverse the array to register each to its own domain name.
        if ($config['domain'] === null) {
            $this->route($config)->name($config['name']);
            return;
        }

        // If its just one domain, we will register it and then exit
        if (is_string($config['domain'])) {
            $this->route($config)->name($config['domain'].'.'.$config['name'])->domain($config['domain']);
            return;
        }

        foreach (Arr::wrap($config['domain']) as $domain) {
            $this->route($config)->name($domain.'.'.$config['name'])->domain($domain);
        }
    }

    /**
     * Returns a Larapoke route
     *
     * @param array $config
     * @return \Illuminate\Routing\Route
     */
    protected function route(array $config)
    {
        return $this->router
            ->match('head', $config['route'])
            ->uses(LarapokeController::class)
            ->middleware($config['middleware']);
    }

}