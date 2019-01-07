<?php

namespace Tests\Unit\Modes;

use DarkGhostHunter\Larapoke\Blade\LarapokeDirective;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\View\Compilers\BladeCompiler;
use Orchestra\Testbench\TestCase;

class ModeMiddlewareTest extends TestCase
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

        $this->app->make('config')->set('larapoke.mode', 'auto');

        $this->app = null;
    }

    protected function setUp()
    {
        parent::setUp();

        LarapokeDirective::setWasRendered(false);

        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make('router');

        $router->group(['middleware' => ['web']], function() use ($router) {
            $router->get('/register', function() {
                return $this->app->make(Factory::class)->make('auth.register');
            })->name('register');
            $router->get('/login', function() {
                return $this->app->make(Factory::class)->make('auth.login');
            })->name('login');
            $router->get('/home', function() {
                return $this->app->make(Factory::class)->make('home');
            })->name('home');
            $router->get('/json', function () {
                return $this->app->make(JsonResponse::class, [
                    'example' => 'name="_token"',
                    'csrf' => 'name="csrf-token"',
                ]);
            });
            $router->get('/form-only', function() { return $this->viewWithFormOnly(); })->name('form-only');
            $router->get('/header-only', function() { return $this->viewWithHeaderOnly(); })->name('header-only');
            $router->get('/nothing', function() { return $this->viewWithNothing(); })->name('nothing');
        });
    }

    protected function viewWithFormOnly()
    {
        /** @var BladeCompiler $blade */
        $blade = $this->app->make(BladeCompiler::class);

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
        /** @var BladeCompiler $blade */
        $blade = $this->app->make(BladeCompiler::class);

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
        /** @var BladeCompiler $blade */
        $blade = $this->app->make(BladeCompiler::class);

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

    protected function tearDown()
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

    public function testDoesntInjectsOnJson()
    {
        $response = $this->get('/json');
        $this->assertNotContains('start-larapoke-script', $response->content());
        $this->assertNotContains('end-larapoke-script', $response->content());
    }

    public function testInjectsScriptOnFormWithHeader()
    {
        $response = $this->get('/register');

        $this->assertContains('start-larapoke-script', $response->content());
        $this->assertContains('end-larapoke-script', $response->content());
    }

    public function testInjectsScriptOnForm()
    {
        $response = $this->get('/form-only');
        $this->assertContains('start-larapoke-script', $response->content());
        $this->assertContains('end-larapoke-script', $response->content());
    }

    public function testInjectsScriptOnHeader()
    {
        $response = $this->get('/header-only');
        $this->assertContains('start-larapoke-script', $response->content());
        $this->assertContains('end-larapoke-script', $response->content());
    }

    public function testInjectsScriptOnNothing()
    {
        $response = $this->get('/nothing');
        $this->assertNotContains('start-larapoke-script', $response->content());
        $this->assertNotContains('end-larapoke-script', $response->content());
    }

}
