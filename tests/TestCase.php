<?php

namespace LukePOLO\LaravelApiMigrations\Tests;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as Orchestra;
use LukePOLO\LaravelApiMigrations\ServiceProvider;
use LukePOLO\LaravelApiMigrations\LaravelApiMigrationsMiddleware;

abstract class TestCase extends Orchestra
{
    public function setUp()
    {
        parent::setUp();
        $this->setupConfig($this->app);
        $this->setUpRoutes($this->app);
        $this->setUpMiddleware();
        $this->setupApiMigrations();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [];
    }

    protected function setupConfig($app)
    {
        $app['config']->set('api-migrations', [

            'path' => 'Http/ApiMigrations',

            'headers' => [
                'api-version'  => 'Api-Version',
            ],

            'current_versions' => [

            ],

            'version_pinning' => false,
        ]);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpRoutes($app)
    {
        Route::get('users/show', function () {
            return [
                'id'     => 123,
                'name'   => [
                    'firstname' => 'Dwight',
                    'lastname'  => 'Schrute',
                ],
                'title'  => 'Assistant to the Regional Manager',
                'skills' => [
                    'bears',
                    'beats',
                    'battlestar galactica',
                ],
            ];
        })->name('show-users');

        Route::post('users', function () {
            return [
                'id'        => 456,
                'firstname' => request('firstname'),
                'lastname'  => request('lastname'),
                'title'     => request('title'),
                'skills'    => request('skills'),
            ];
        })->name('create-user');

        $app['router']->getRoutes()->refreshNameLookups();
    }

    protected function setUpMiddleware()
    {
        $this->app[Kernel::class]->pushMiddleware(LaravelApiMigrationsMiddleware::class);
    }

    protected function setupApiMigrations()
    {
        \File::copyDirectory(__DIR__.'/ApiMigrations', app_path().'/Http/ApiMigrations');
    }
}
