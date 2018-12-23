<?php

namespace Tests;

use DarkGhostHunter\Larapoke\Blade\LarapokeDirective;
use DarkGhostHunter\Larapoke\Http\Controllers\LarapokeController;
use DarkGhostHunter\Larapoke\Http\Middleware\LarapokeMiddleware;
use Illuminate\Contracts\Http\Kernel;
use Orchestra\Testbench\TestCase;

class LarapokeServiceProviderTest extends TestCase
{

    protected $backupStaticAttributesBlacklist = [
        LarapokeDirective::class => [
            'view', 'config'
        ]
    ];

    protected function getPackageProviders($app)
    {
        return [
          'DarkGhostHunter\Larapoke\LarapokeServiceProvider'
        ];
    }

    protected function getEnvironmentSetUp($app)
    {

        /** @var \Illuminate\Routing\Router $router */
        $router = $app->make('router');

        $router->group(['web'], function() use ($router) {
            $router->get('/test', function () {
                return 'ok';
            });
        });
    }

    public function testReceivesDefaultConfig()
    {
        $this->assertEquals(
            include __DIR__ . '/../config/larapoke.php',
            $this->app['config']['larapoke']
        );
    }

    public function testPublishesConfigFile()
    {
        $this->artisan('vendor:publish', [
            '--provider' => 'DarkGhostHunter\Larapoke\LarapokeServiceProvider'
        ]);

        $this->assertFileExists(config_path('larapoke.php'));
        $this->assertFileIsReadable(config_path('larapoke.php'));
        $this->assertFileEquals(config_path('larapoke.php'), __DIR__ . '/../config/larapoke.php');
        $this->assertTrue(unlink(config_path('larapoke.php')));
    }

    public function testLoadDefaultRoute()
    {
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make('router');

        /** @var \Illuminate\Routing\Route $route */
        $route = $router->getRoutes()->match(
            $this->app->make('request')->create('/poke', 'HEAD')
        );

        $this->assertEquals('larapoke', $route->getName());
        $this->assertInstanceOf(LarapokeController::class, $route->getController());
    }

    public function testLoadDefaultView()
    {
        $script = $this->app->make('view')
            ->make('larapoke::script')
            ->with([
                'route' => '/poke',
                'interval' => 100,
                'timeout' => true,
                'lifetime' => 400000,
            ])
            ->render();

        $this->assertIsString($script);
        $this->assertContains('larapoke_', $script);
    }

    public function testRegistersMiddleware()
    {
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make('router');

        $this->assertArrayHasKey('larapoke', $router->getMiddleware());
        $this->assertTrue($this->app->make(Kernel::class)->hasMiddleware(LarapokeMiddleware::class));
    }

    public function testRegistersBladeDirective()
    {
        /** @var \Illuminate\View\Factory $view */
        $view = $this->app->make('view');

        $directives = $view->getEngineResolver()
            ->resolve('blade')
            ->getCompiler()
            ->getCustomDirectives();

        $this->assertArrayHasKey('larapoke', $directives);
    }
}
