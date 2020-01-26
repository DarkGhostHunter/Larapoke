<?php

namespace Tests\Unit\Routes;

use PHPUnit\Framework\TestCase;
use DarkGhostHunter\Larapoke\Http\RouteGenerator;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class RouteGeneratorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var \Illuminate\Contracts\Config\Repository & \Mockery\MockInterface */
    protected $config;

    /** @var \Illuminate\Routing\Router & \Mockery\MockInterface */
    protected $router;

    protected function setUp() : void
    {
        $this->config = \Mockery::spy(\Illuminate\Contracts\Config\Repository::class);
        $this->router = \Mockery::spy(\Illuminate\Routing\Router::class);

        $this->config->shouldReceive('get')
            ->once()
            ->with('larapoke.poking.route')
            ->andReturn($route = 'test-poke');
        $this->config->shouldReceive('get')
            ->once()
            ->with('larapoke.poking.name')
            ->andReturn('test-name');

        $this->config->shouldReceive('get')
            ->once()
            ->with('larapoke.poking.middleware')
            ->andReturn('test-middleware');
    }

    public function testSetGlobalRoute()
    {

        $this->config->shouldReceive('get')
            ->once()
            ->with('larapoke.poking.domain')
            ->andReturn(null);


        $this->router->shouldReceive('match')
            ->once()
            ->with('head', 'test-poke')
            ->andReturnSelf();
        $this->router->shouldReceive('name')
            ->once()
            ->with('test-name')
            ->andReturnSelf();
        $this->router->shouldReceive('uses')
            ->once()
            ->with('DarkGhostHunter\Larapoke\Http\Controllers\LarapokeController')
            ->andReturnSelf();
        $this->router->shouldReceive('middleware')
            ->once()
            ->with('test-middleware')
            ->andReturnSelf();

        $generator = new RouteGenerator($this->router, $this->config);
        $generator->setRoutes();

        $this->config->shouldHaveReceived('get')
            ->with('larapoke.poking.domain')
            ->once();

        $this->router->shouldHaveReceived('match')
            ->with('head', 'test-poke')
            ->once();
        $this->router->shouldHaveReceived('name')
            ->with('test-name')
            ->once();
        $this->router->shouldHaveReceived('uses')
            ->with('DarkGhostHunter\Larapoke\Http\Controllers\LarapokeController')
            ->once();
        $this->router->shouldHaveReceived('middleware')
            ->with('test-middleware')
            ->once();

    }

    public function testSetOneDomainRoute()
    {
        $this->config->shouldReceive('get')
            ->once()
            ->with('larapoke.poking.domain')
            ->andReturn('one');

        $this->router->shouldReceive('match')
            ->once()
            ->with('head', 'test-poke')
            ->andReturnSelf();
        $this->router->shouldReceive('uses')
            ->once()
            ->with('DarkGhostHunter\Larapoke\Http\Controllers\LarapokeController')
            ->andReturnSelf();
        $this->router->shouldReceive('middleware')
            ->once()
            ->with('test-middleware')
            ->andReturnSelf();
        $this->router->shouldReceive('name')
            ->once()
            ->with("one.test-name")
            ->andReturnSelf();

        $generator = new RouteGenerator($this->router, $this->config);
        $generator->setRoutes();

        $this->config->shouldHaveReceived('get')
            ->with('larapoke.poking.domain')
            ->once();

        $this->router->shouldHaveReceived('match')
            ->with('head', 'test-poke')
            ->once();
        $this->router->shouldHaveReceived('uses')
            ->with('DarkGhostHunter\Larapoke\Http\Controllers\LarapokeController')
            ->once();
        $this->router->shouldHaveReceived('middleware')
            ->with('test-middleware')
            ->once();
        $this->router->shouldHaveReceived('name')
            ->with('one.test-name')
            ->once();

    }

    public function testSetMultipleDomainRoutes()
    {
        $this->config->shouldReceive('get')
            ->once()
            ->with('larapoke.poking.domain')
            ->andReturn($domains = [
                'one', 'two', 'three'
            ]);

        $this->router->shouldReceive('match')
            ->times(count($domains))
            ->with('head', 'test-poke')
            ->andReturnSelf();
        $this->router->shouldReceive('uses')
            ->times(count($domains))
            ->with('DarkGhostHunter\Larapoke\Http\Controllers\LarapokeController')
            ->andReturnSelf();
        $this->router->shouldReceive('middleware')
            ->times(count($domains))
            ->with('test-middleware')
            ->andReturnSelf();

        foreach ($domains as $domain) {
            $this->router->shouldReceive('name')
                ->once()
                ->with("$domain.test-name")
                ->andReturnSelf();
        }

        $generator = new RouteGenerator($this->router, $this->config);
        $generator->setRoutes();

        $this->config->shouldHaveReceived('get')
            ->with('larapoke.poking.domain')
            ->once();

        $this->router->shouldHaveReceived('match')
            ->with('head', 'test-poke')
            ->times(count($domains));
        $this->router->shouldHaveReceived('uses')
            ->with('DarkGhostHunter\Larapoke\Http\Controllers\LarapokeController')
            ->times(count($domains));
        $this->router->shouldHaveReceived('middleware')
            ->with('test-middleware')
            ->times(count($domains));

        foreach ($domains as $domain) {
            $this->router->shouldHaveReceived('name')
                ->with("$domain.test-name")
                ->once();
        }
    }

    protected function tearDown() : void
    {
        parent::tearDown();

        \Mockery::close();
    }
}
