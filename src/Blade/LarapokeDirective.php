<?php

namespace DarkGhostHunter\Larapoke\Blade;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\Factory;

class LarapokeDirective implements Htmlable
{
    /**
     * The configuration for the Blade Directive
     *
     * @var Repository
     */
    protected Repository $config;

    /**
     * The View Factory Instance
     *
     * @var Factory
     */
    protected Factory $view;

    /**
     * URL Generator
     *
     * @var UrlGenerator
     */
    protected UrlGenerator $url;

    /**
     * LarapokeDirective constructor.
     *
     * We use static variables son each Larapoke call doesn't get everything all again.
     *
     * @param  \Illuminate\Contracts\Config\Repository  $config
     * @param  \Illuminate\Contracts\View\Factory  $view
     * @param  \Illuminate\Contracts\Routing\UrlGenerator  $url
     */
    public function __construct(Repository $config, Factory $view, UrlGenerator $url)
    {
        $this->view = $view;
        $this->config = $config;
        $this->url = $url;
    }

    /**
     * Parse de Config and save it
     *
     * @return array
     */
    protected function parseConfig(): array
    {
        $session = $this->config->get('session.lifetime') * 60 * 1000;

        return [
            'route'    => $this->url->to($this->config->get('larapoke.poking.route')),
            'interval' => (int)($session / $this->config->get('larapoke.times')),
            'lifetime' => $session,
        ];
    }

    /**
     * Renders the scripts using the Larapoke configuration
     *
     * @return string
     */
    public function toHtml(): string
    {
        return $this->view->make($this->config->get('larapoke.view'), $this->parseConfig())->render();
    }
}