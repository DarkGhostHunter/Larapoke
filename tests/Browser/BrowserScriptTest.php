<?php

namespace Tests\Browser;

use Illuminate\Contracts\View\Factory;
use Orchestra\Testbench\Dusk\TestCase;

class BrowserScriptTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [
            'DarkGhostHunter\Larapoke\LarapokeServiceProvider',
        ];
    }

    protected function getEnvironmentSetUp($app)
    {

        $this->app = $app;

        $app['config']->set('session.lifetime', 1);

        $this->artisan('make:auth', [
            '--force' => true,
            '--views' => true,
        ])->run();

        $app['router']->group(['middleware' => ['web']], function () use ($app) {
            $app['router']->get('/register', function() {
                return $this->app->make(Factory::class)->make('auth.register');
            })->name('register');
            $app['router']->post('/register', function() {
                return \Request::all();
            })->name('register');
            $app['router']->get('/login', function() {
                return $this->app->make(Factory::class)->make('auth.login');
            })->name('login');
        });

        $this->app = null;
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

    public function testPokeWorks()
    {
        $this->browse(function ($first, $second) {
            /** @var \Laravel\Dusk\Browser $first */
            $first->visit('/register')
                ->type('name', 'test-email')
                ->type('email', 'email@email.com')
                ->type('password', 'test-password')
                ->type('password_confirmation', 'test-password')
                ->press('Register')
                ->assertSee('test-email')
                ->assertSee('email@email.com')
                ->assertSee('test-password');

            /** @var \Laravel\Dusk\Browser $second */
            $second->visit('/register')
                ->pause(65000)
                ->type('name', 'test-email')
                ->type('email', 'email@email.com')
                ->type('password', 'test-password')
                ->type('password_confirmation', 'test-password')
                ->press('Register')
                ->assertSee('test-email')
                ->assertSee('email@email.com')
                ->assertSee('test-password');
        });
    }
}