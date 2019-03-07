<?php

namespace DarkGhostHunter\Larapoke\Blade;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\View\Factory;

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
     * If the directive was rendered
     *
     * @var bool
     */
    protected static $wasRendered = false;

    /**
     * The Config for the Blade Directive
     *
     * @var array
     */
    protected $config;

    /**
     * The View Factory Instance
     *
     * @var Factory
     */
    protected $view;

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
        $this->view = $view;
        $this->config = $config;
    }

    /**
     * Returns if the script was already rendered
     *
     * @return bool
     */
    public static function getWasRendered()
    {
        return self::$wasRendered;
    }

    /**
     * Set if the script should render again
     *
     * @param bool $wasRendered
     */
    public static function setWasRendered(bool $wasRendered)
    {
        self::$wasRendered = $wasRendered;
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
            'route' => $this->config->get('larapoke.poking.route'),
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
        self::$wasRendered = true;

        return $this->view->make(
            $this->config->get('larapoke.view'),
            $this->parseConfig()
        )->render();
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
        return static::$wasRendered ? '' : $this->renderScript();
    }
}