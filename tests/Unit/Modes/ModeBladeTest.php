<?php

namespace Tests\Unit\Modes;

use DarkGhostHunter\Larapoke\Blade\LarapokeDirective;
use Orchestra\Testbench\TestCase;

class ModeBladeTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            'DarkGhostHunter\Larapoke\LarapokeServiceProvider'
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->app = $app;

        $this->artisan('make:auth', [
            '--force' => true,
            '--views' => true,
        ])->run();

        $this->app->make('config')->set('larapoke.mode', 'blade');

        $this->app = null;
    }

    protected function setUp() : void
    {
        parent::setUp();

        LarapokeDirective::setWasRendered(false);

        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make('router');

        $router->group(['middleware' => ['web']], function() use ($router) {
            $router->get('/register', function() {
                return $this->app->make(\Illuminate\Contracts\View\Factory::class)->make('auth.register');
            })->name('register');
            $router->get('/login', function() {
                return $this->app->make(\Illuminate\Contracts\View\Factory::class)->make('auth.login');
            })->name('login');
            $router->get('/home', function() {
                return $this->app->make(\Illuminate\Contracts\View\Factory::class)->make('home');
            })->name('home');
            $router->get('/form-only', function() { return $this->viewWithFormOnly(); })->name('form-only');
            $router->get('/multiple-form', function() { return $this->viewMultipleForms(); })->name('multiple-form');
            $router->get('/multiple-form-with-middleware', function() { return $this->viewMultipleForms(); })
                ->name('multiple-form-with-middleware')->middleware('larapoke');
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
                        ' . csrf_field() . ' @larapoke
                    </form>
                </body>
            </html>
        ');
    }

    protected function viewMultipleForms()
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
                    <form action="/register" method="post">
                        ' . csrf_field() . ' @larapoke
                    </form>
                    
                    <form action="/login" method="post">
                        ' . csrf_field() . ' @larapoke
                    </form>
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

        $this->recurseRmdir(resource_path('views/auth'));
        $this->recurseRmdir(resource_path('views/layouts'));
    }

    protected function recurseRmdir($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->recurseRmdir("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    public function testNoScriptOnRouteWithoutMiddleware()
    {
        $response = $this->get('/register');
        $this->assertStringNotContainsString('start-larapoke-script', $response->content());
        $this->assertStringNotContainsString('end-larapoke-script', $response->content());
    }

    public function testInjectsScriptOnForm()
    {
        $response = $this->get('/form-only');
        $this->assertStringContainsString('start-larapoke-script', $response->content());
        $this->assertStringContainsString('end-larapoke-script', $response->content());
    }

    public function testInjectsOnceOnMultipleForms()
    {
        $response = $this->get('/multiple-form');

        $this->assertStringContainsString('start-larapoke-script', $response->content());
        $this->assertStringContainsString('end-larapoke-script', $response->content());

        $this->assertTrue(substr_count($response->content(), 'start-larapoke-script') === 1);
        $this->assertTrue(substr_count($response->content(), 'end-larapoke-script') === 1);
    }

    public function testInjectsOnceOnMiddlewareAndMultipleForms()
    {
        $response = $this->get('/multiple-form-with-middleware');

        $this->assertStringContainsString('start-larapoke-script', $response->content());
        $this->assertStringContainsString('end-larapoke-script', $response->content());

        $this->assertTrue(substr_count($response->content(), 'start-larapoke-script') === 1);
        $this->assertTrue(substr_count($response->content(), 'end-larapoke-script') === 1);
    }


}
