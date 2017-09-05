<?php

namespace LukePOLO\LaravelApiMigrations\Tests;

use Illuminate\Support\Facades\Artisan;
use LukePOLO\LaravelApiMigrations\ServiceProvider;

class CommandsTest extends TestCase
{
    /** @test */
    public function it_will_get_releases_from_cache()
    {
        Artisan::call('cache:api-migrations');

        $this->assertFileExists(base_path(ServiceProvider::REQUEST_MIGRATIONS_CACHE));
    }

    /** @test */
    public function it_can_delete_cache()
    {
        Artisan::call('cache:api-migrations');

        $this->assertFileExists(base_path(ServiceProvider::REQUEST_MIGRATIONS_CACHE));

        Artisan::call('clear:api-migrations');

        $this->assertFileNotExists(base_path(ServiceProvider::REQUEST_MIGRATIONS_CACHE));
    }
}
