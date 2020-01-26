<?php

namespace Tests\Browser;

use Tests\ScaffoldAuth;
use Illuminate\Support\Str;
use Tests\RegistersPackages;
use Illuminate\Contracts\View\Factory;
use Orchestra\Testbench\Dusk\TestCase;

class BrowserScriptTest extends TestCase
{
    use RegistersPackages;
    use ScaffoldAuth;

    protected function getEnvironmentSetUp($app)
    {
        $this->app = $app;

        $app['config']->set('session.lifetime', 1);
        $app['config']->set('session.expire_on_close', true);
        $app['config']->set('app.key', Str::random(32));

        $this->scaffoldAuth($app);

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

    protected function tearDown() : void
    {
        parent::tearDown();

        $this->cleanScaffold();
    }

    public function testPokeWorks()
    {
        $this->browse(function ($first, $second) {
            /** @var \Laravel\Dusk\Browser $first */
            $first->visit('/register')
                ->type('#name', 'test-email')
                ->type('#email', 'email@email.com')
                ->type('#password', 'test-password')
                ->type('#password-confirm', 'test-password')
                ->press('Register')
                ->assertSee('test-email')
                ->assertSee('email@email.com')
                ->assertSee('test-password');

            /** @var \Laravel\Dusk\Browser $second */
            $second->visit('/register')
                ->pause(65000)
                ->type('#name', 'test-email')
                ->type('#email', 'email@email.com')
                ->type('#password', 'test-password')
                ->type('#password-confirm', 'test-password')
                ->press('Register')
                ->assertSee('test-email')
                ->assertSee('email@email.com')
                ->assertSee('test-password');
        });
    }
}