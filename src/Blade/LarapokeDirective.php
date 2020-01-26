<?php

namespace DarkGhostHunter\Larapoke\Blade;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Routing\UrlGenerator;

/**
 * Class LarapokeDirective
 *
 * This directive is in charge of creating the script
 *
 * @package DarkGhostHunter\Larapoke\Blade
 */
class LarapokeDirective
{
    /**
     * If the directive was rendered already.
     *
     * @var bool
     */
    protected $wasRendered = false;

    /**
     * The configuration for the Blade Directive
     *
     * @var Repository
     */
    protected $config;

    /**
     * The View Factory Instance
     *
     * @var Factory
     */
    protected $view;

    /**
     * URL Generator
     *
     * @var UrlGenerator
     */
    protected $url;

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
    protected function parseConfig()
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
    public function renderScript()
    {
        $this->wasRendered = true;

        return $this->view->make($this->config->get('larapoke.view'), $this->parseConfig())->render();
    }

    /**
     * Returns the rendered script
     *
     * @return string
     */
    public function getRenderedScript()
    {
        // Rendering the script isn't costly, but doing it multiple times in page
        // is redundant. When called multiple times, we will render the first
        // instance, and return an empty string on the subsequent renders.
        return $this->wasRendered ? '' : $this->renderScript();
    }
}