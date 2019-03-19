<?php

namespace Tests\Unit\Script;

use DarkGhostHunter\Larapoke\Blade\LarapokeDirective;
use Illuminate\Routing\UrlGenerator;
use Orchestra\Testbench\TestCase;

class ScriptTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            'DarkGhostHunter\Larapoke\LarapokeServiceProvider'
        ];
    }

    /** @var \Illuminate\Config\Repository & \Mockery\MockInterface */
    protected $mockConfig;

    /** @var \Illuminate\View\Factory & \Mockery\MockInterface */
    protected $mockView;

    /** @var \Illuminate\Contracts\Routing\UrlGenerator & \Mockery\MockInterface */
    protected $mockUrl;

    protected $sessionLifetime;

    protected $times;

    public function setUp() : void
    {
        $this->mockConfig = \Mockery::mock(\Illuminate\Config\Repository::class);

        $this->mockView = \Mockery::mock(\Illuminate\View\Factory::class);

        $this->mockUrl = \Mockery::spy(\Illuminate\Contracts\Routing\UrlGenerator::class);

        LarapokeDirective::setWasRendered(false);
    }

    public function testSetAndGetWasRendered()
    {
        $this->assertFalse(LarapokeDirective::getWasRendered());
        LarapokeDirective::setWasRendered(true);
        $this->assertTrue(LarapokeDirective::getWasRendered());
        LarapokeDirective::setWasRendered(false);
        $this->assertFalse(LarapokeDirective::getWasRendered());
    }

    public function testReceivesConfig()
    {
        $this->mockView
            ->shouldReceive('make')
            ->with('custom-larapoke-view', \Mockery::type('array'))
            ->andReturnUsing(function ($script, $config) {
                return (new class ($config)
                {
                    protected $config;

                    public function __construct($config)
                    {
                        $this->config = $config;
                    }

                    public function render()
                    {
                        return $this->config;
                    }
                });
            });

        $this->mockConfig->shouldReceive('get')
            ->with('session.lifetime')
            ->andReturn($this->sessionLifetime = rand(10, 240));

        $this->mockConfig->shouldReceive('get')
            ->with('larapoke.poking.route')
            ->andReturn($route = 'test-larapoke-route');

        $this->mockConfig->shouldReceive('get')
            ->with('larapoke.times')
            ->andReturn($this->times = rand(2, 16));

        $this->mockConfig->shouldReceive('get')
            ->with('larapoke.view')
            ->andReturn('custom-larapoke-view');


        $this->mockUrl->shouldReceive('to')
            ->once()
            ->with($route)
            ->andReturn('http://test-app.com/'.$route);

        $script = (new LarapokeDirective(
            $this->mockConfig, $this->mockView, $this->mockUrl)
        )->getRenderedScript();

        $this->assertEquals(
            'http://test-app.com/test-larapoke-route',
            $script['route']
        );
        $this->assertEquals(
            (int)((($this->sessionLifetime * 60 * 1000) / $this->times)),
            $script['interval']
        );
        $this->assertEquals(
            $this->sessionLifetime * 60 * 1000,
            $script['lifetime']
        );
    }
}