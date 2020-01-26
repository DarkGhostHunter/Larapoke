<?php

namespace Tests\Unit\Script;

use Tests\ScaffoldAuth;
use Tests\RegistersPackages;
use Orchestra\Testbench\TestCase;
use Illuminate\Contracts\View\Factory;

class ScriptRouteTest extends TestCase
{
    use RegistersPackages;
    use ScaffoldAuth;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('session.lifetime', 90);

        $app['config']->set('larapoke', [
            'mode' => 'auto',
            'times' => 8,
            'timeout' => false,
            'poking' => [
                'route' => 'test-larapoke-route',
                'name' => 'test-larapoke-name',
                'domain' => 'test-subdomain.app.com',
                'middleware' => ['web', 'testgroup'],
            ]
        ]);
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->scaffoldAuth($app);

        $app->bind('testgroup', function() {
            return new class() {
                public function handle($request, $next)
                {
                    return $next($request);
                }
            };
        });
    }

    protected function tearDown() : void
    {
        parent::tearDown();

        $this->cleanScaffold();
    }

    protected function setUp() : void
    {
        parent::setUp();

        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make('router');

        $router->group(['middleware' => ['web']], function () use ($router) {
            $router->get('/register', function () {
                return $this->app->make(Factory::class)->make('auth.register');
            })->name('register');
            $router->get('/login', function () {
                return $this->app->make(Factory::class)->make('auth.login');
            })->name('login');
            $router->get('/home', function () {
                return $this->app->make(Factory::class)->make('home');
            })->name('home');
        });
    }

    public function testPokeExpired()
    {
        $content = $this->get('/register')->content();

        $matches = [];

        preg_match(
            '/<meta name="csrf-token" content="(.*?)">/',
            $content,
            $matches
        );

        $csrfToken = $matches[1];

        $this->app->make('session')->flush();

        $response = $this->get('/test-larapoke-route', [
            '_token' => $csrfToken,
        ]);

        $response->assertStatus(404);
    }

    public function testDifferentRouteAndSubdomain()
    {
        $request = $this->call(
            'HEAD',
            'http://test-subdomain.app.com/test-larapoke-route', [], [], [],
            $this->transformHeadersToServerVars([])
        );

        $request->assertStatus(204);
        $this->assertEmpty($request->content());
    }

    public function testWrongMethodGives405()
    {
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method) {

            $request = $this->call(
                $method,
                'http://test-subdomain.app.com/test-larapoke-route', [], [], [],
                $this->transformHeadersToServerVars([])
            );
            $request->assertStatus(405);
        }
    }

    public function testHasNamedRoute()
    {
        $this->assertTrue(
            $this->app->make('router')->getRoutes()->hasNamedRoute('test-subdomain.app.com.test-larapoke-name')
        );
    }

    public function testHasMiddlewareGroup()
    {
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make('router');

        $route = $router->getRoutes()->getByName('test-subdomain.app.com.test-larapoke-name');

        $this->assertTrue(in_array('testgroup', $route->getAction('middleware')));
        $this->assertTrue(in_array('web', $route->getAction('middleware')));
    }
}