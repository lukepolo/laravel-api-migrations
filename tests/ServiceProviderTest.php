<?php

namespace LukePOLO\LaravelApiMigrations\Tests;

use Illuminate\Support\Facades\Artisan;
use LukePOLO\LaravelApiMigrations\Migrator;
use LukePOLO\LaravelApiMigrations\ServiceProvider;
use ReflectionClass;

class ServiceProviderTest extends TestCase
{
    /** @test */
    public function it_will_get_migrator_from_app()
    {
        $this->assertEquals(new Migrator, $this->app->make('laravel-api-migrations'));
    }

    /** @test */
    public function it_will_get_collection_with_no_releases()
    {
        $this->app['config']->set('api-migrations', [
            'path' => 'Nope'
        ]);

        $serviceProvider = new ServiceProvider($this->app);
        $serviceProviderReflection = new ReflectionClass($serviceProvider);
        $method = $serviceProviderReflection->getMethod('generateApiDetails');
        $method->setAccessible(true);

        $this->assertEquals(collect(), $method->invoke($serviceProvider));
    }

    /** @test */
    public function it_will_get_releases_from_cache()
    {
        Artisan::call('cache:api-migrations');

        $serviceProvider = new ServiceProvider($this->app);
        $serviceProviderReflection = new ReflectionClass($serviceProvider);
        $method = $serviceProviderReflection->getMethod('generateApiDetails');
        $method->setAccessible(true);

        $cached = $method->invoke($serviceProvider);

        Artisan::call('clear:api-migrations');

        $this->assertEquals($method->invoke($serviceProvider), $cached);
    }
}
