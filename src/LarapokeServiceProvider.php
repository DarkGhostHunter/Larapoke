<?php

namespace DarkGhostHunter\Larapoke;

use Illuminate\Routing\Router;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\View\Compilers\BladeCompiler;
use DarkGhostHunter\Larapoke\Blade\LarapokeDirective;
use DarkGhostHunter\Larapoke\Http\Middleware\LarapokeMiddleware;
use DarkGhostHunter\Larapoke\Http\Middleware\LarapokeGlobalMiddleware;

class LarapokeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/larapoke.php', 'larapoke');

        $this->app->singleton(LarapokeDirective::class, function ($app) {
            return new LarapokeDirective($app['config'], $app['view'], $app['url']);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @param  \Illuminate\Contracts\Config\Repository  $config
     * @param  \Illuminate\View\Compilers\BladeCompiler  $blade
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(Router $router, Repository $config, BladeCompiler $blade)
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/larapoke.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'larapoke');

        $this->publishes([
            __DIR__ . '/../config/larapoke.php' => config_path('larapoke.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/larapoke'),
        ]);

        $this->bootMiddleware($router, $config);

        $this->bootBladeDirective($blade);
    }

    /**
     * Registers (or push globally) the Middleware
     *
     * @param  \Illuminate\Routing\Router  $router
     * @param  \Illuminate\Contracts\Config\Repository  $config
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function bootMiddleware(Router $router, Repository $config)
    {
        $router->aliasMiddleware('larapoke', LarapokeMiddleware::class);

        // If Larapoke is set to auto, push the global middleware.
        if ($config->get('larapoke.mode') === 'auto') {
            $this->app->make(Kernel::class)->pushMiddleware(LarapokeGlobalMiddleware::class);
        }
    }

    /**
     * Registers the Blade Directive
     *
     * @param  \Illuminate\View\Compilers\BladeCompiler  $blade
     * @return void
     */
    protected function bootBladeDirective(BladeCompiler $blade)
    {
        $blade->directive('larapoke', function () {
            return $this->app->make(LarapokeDirective::class)->getRenderedScript();
        });
    }
}