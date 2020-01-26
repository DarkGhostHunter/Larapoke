<?php

namespace Tests\Unit\Modes;

use Tests\ScaffoldAuth;
use Tests\RegistersPackages;
use Illuminate\Http\JsonResponse;
use Orchestra\Testbench\TestCase;
use Illuminate\Contracts\View\Factory;
use DarkGhostHunter\Larapoke\Blade\LarapokeDirective;

class ModeMiddlewareTest extends TestCase
{
    use RegistersPackages;
    use ScaffoldAuth;

    protected function getEnvironmentSetUp($app)
    {
        $this->scaffoldAuth($app);

        $app->make('config')->set('larapoke.mode', 'middleware');
    }

    protected function setUp() : void
    {
        parent::setUp();

        LarapokeDirective::setWasRendered(false);

        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make('router');

        $router->group(['middleware' => ['web']], function() use ($router) {
            $router->get('/register', function() {
                return $this->app->make(Factory::class)->make('auth.register');
            })->name('register')->middleware('larapoke:detect');
            $router->get('/login', function() {
                return $this->app->make(Factory::class)->make('auth.login');
            })->name('login')->middleware('larapoke');
            $router->get('/json', function () {
                return $this->app->make(JsonResponse::class, [
                    'example' => 'name="_token"',
                    'csrf' => 'name="csrf-token"',
                ]);
            })->middleware('larapoke');
            $router->get('/form-only', function() { return $this->viewWithFormOnly(); })
                ->name('form-only')->middleware('larapoke:detect');
            $router->get('/header-only', function() { return $this->viewWithHeaderOnly(); })
                ->name('header-only')->middleware('larapoke:detect');
            $router->get('/nothing', function() { return $this->viewWithNothing(); })
                ->name('nothing')->middleware('larapoke:detect');
            $router->get('/nothing-with-middleware', function() { return $this->viewWithNothing(); })
                ->name('nothing')->middleware('larapoke');
            $router->get('/no-middleware', function() { return $this->viewWithNothing(); })
                ->name('nothing');
        });
    }

    protected function viewWithFormOnly()
    {
        /** @var \Illuminate\View\Compilers\BladeCompiler $blade */
        $blade = $this->app->make(\Illuminate\View\Compilers\BladeCompiler::class);

        return $blade->compileString('
            <!doctype html>
            <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
                             <meta http-equiv="X-UA-Compatible" content="ie=edge">
                    <title>Document</title>
                </head>
                <body>
                    <form action="/register" method="post">
                        ' . csrf_field() . '
                    </form>
                </body>
            </html>
        ');
    }

    protected function viewWithHeaderOnly()
    {
        /** @var \Illuminate\View\Compilers\BladeCompiler $blade */
        $blade = $this->app->make(\Illuminate\View\Compilers\BladeCompiler::class);

        return $blade->compileString('
            <!doctype html>
            <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="csrf-token" content="' . e(csrf_token()) . '">
                    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
                             <meta http-equiv="X-UA-Compatible" content="ie=edge">
                    <title>Document</title>
                </head>
                <body>
                </body>
            </html>
        ');
    }

    protected function viewWithNothing()
    {
        /** @var \Illuminate\View\Compilers\BladeCompiler $blade */
        $blade = $this->app->make(\Illuminate\View\Compilers\BladeCompiler::class);

        return $blade->compileString('
            <!doctype html>
            <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
                             <meta http-equiv="X-UA-Compatible" content="ie=edge">
                    <title>Document</title>
                </head>
                <body>
                </body>
            </html>
        ');
    }

    protected function tearDown() : void
    {
        parent::tearDown();

        $this->cleanScaffold();
    }

    public function testDoesntInjectsOnJson()
    {
        $response = $this->get('/json');

        $this->assertStringNotContainsString('start-larapoke-script', $response->content());
        $this->assertStringNotContainsString('end-larapoke-script', $response->content());
    }


    public function testNoScriptOnNoMiddleware()
    {
        $response = $this->get('/no-middleware');
        $this->assertStringNotContainsString('start-larapoke-script', $response->content());
        $this->assertStringNotContainsString('end-larapoke-script', $response->content());
    }

    public function testDetectsHeaderOrForm()
    {
        $response = $this->get('/register');
        $this->assertStringContainsString('start-larapoke-script', $response->content());
        $this->assertStringContainsString('end-larapoke-script', $response->content());
    }

    public function testDetectsHeader()
    {
        $response = $this->get('/header-only');
        $this->assertStringContainsString('start-larapoke-script', $response->content());
        $this->assertStringContainsString('end-larapoke-script', $response->content());
    }

    public function testDetectsForm()
    {
        $response = $this->get('/form-only');
        $this->assertStringContainsString('start-larapoke-script', $response->content());
        $this->assertStringContainsString('end-larapoke-script', $response->content());
    }

    public function testDetectsNothing()
    {
        $response = $this->get('/nothing');
        $this->assertStringNotContainsString('start-larapoke-script', $response->content());
        $this->assertStringNotContainsString('end-larapoke-script', $response->content());
    }

    public function testInjectsForcefullyWithoutDetect()
    {
        $response = $this->get('/nothing-with-middleware');
        $this->assertStringContainsString('start-larapoke-script', $response->content());
        $this->assertStringContainsString('end-larapoke-script', $response->content());

        LarapokeDirective::setWasRendered(false);

        $response = $this->get('/login');

        $this->assertStringContainsString('start-larapoke-script', $response->content());
        $this->assertStringContainsString('end-larapoke-script', $response->content());
    }

    public function testDoesntInjectsOnExceptionResponse()
    {
        $response = $this->get('non-existant-route-triggers-exception');

        $response->assertDontSee('start-larapoke-script');
    }

}
