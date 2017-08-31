<?php

namespace LukePOLO\LaravelApiMigrations\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelApiMigrations extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-api-migrations';
    }
}
