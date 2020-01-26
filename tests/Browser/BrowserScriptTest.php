<?php

namespace Tests\Browser;

use Tests\ScaffoldAuth;
use Illuminate\Support\Str;
use Tests\RegistersPackages;
use Illuminate\Contracts\View\Factory;
use Orchestra\Testbench\Dusk\TestCase;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Orchestra\Testbench\Dusk\Options as DuskOptions;

class BrowserScriptTest extends TestCase
{
    use RegistersPackages;
    use ScaffoldAuth;

    protected function setUp() : void
    {
        $this->afterApplicationCreated(function () {
            $this->app['config']->set('session.lifetime', 1);
            $this->app['config']->set('session.expire_on_close', true);
            $this->app['config']->set('app.key', Str::random(32));

            $this->scaffoldAuth($this->app);

            $this->app['router']->group(['middleware' => ['web']], function ()  {
                $this->app['router']->get('register', function ()  {
                    return $this->app->make(Factory::class)->make('auth.register');
                })->name('register');
                $this->app['router']->post('register', function () {
                    return \Request::all();
                })->name('register');
                $this->app['router']->get('login', function ()  {
                    return $this->app->make(Factory::class)->make('auth.login');
                })->name('login');
            });
        });

        parent::setUp();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver() : RemoteWebDriver
    {
        return RemoteWebDriver::create(
            'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                DuskOptions::getChromeOptions()->addArguments(['--disable-gpu', '--headless'])
            )
        );
    }

    protected function tearDown() : void
    {
        $this->cleanScaffold();

        parent::tearDown();
    }

    public function testPokeWorks()
    {
        $this->browse(function (\Laravel\Dusk\Browser $first, \Laravel\Dusk\Browser $second) {
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