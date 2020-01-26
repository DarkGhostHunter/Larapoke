<?php

namespace Tests\Unit;

use Tests\RegistersPackages;
use Orchestra\Testbench\TestCase;
use Illuminate\Contracts\Http\Kernel;
use DarkGhostHunter\Larapoke\Blade\LarapokeDirective;
use DarkGhostHunter\Larapoke\Http\Controllers\LarapokeController;
use DarkGhostHunter\Larapoke\Http\Middleware\LarapokeGlobalMiddleware;

class LarapokeServiceProviderTest extends TestCase
{
    use RegistersPackages;

    protected $backupStaticAttributesBlacklist = [
        LarapokeDirective::class => [
            'view', 'config'
        ]
    ];

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
            include __DIR__ . '/../../config/larapoke.php',
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
        $this->assertFileEquals(config_path('larapoke.php'), __DIR__ . '/../../config/larapoke.php');
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
        $this->assertStringContainsString('larapoke_', $script);
    }

    public function testRegistersGlobalMiddleware()
    {
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make('router');

        $this->assertTrue($this->app->make(Kernel::class)->hasMiddleware(LarapokeGlobalMiddleware::class));
    }

    public function testRegistersMiddlewareAlias()
    {
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make('router');

        $this->assertArrayHasKey('larapoke', $router->getMiddleware());
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
