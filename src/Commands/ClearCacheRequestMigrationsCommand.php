<?php

namespace LukePOLO\LaravelApiMigrations\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use LukePOLO\LaravelApiMigrations\ServiceProvider;

class ClearCacheRequestMigrationsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'clear:api-migrations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes the cache for API migrations';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $requestMigrationsCache = base_path(ServiceProvider::REQUEST_MIGRATIONS_CACHE);

        File::delete($requestMigrationsCache);

        $this->info('Api Migrations Have Been Deleted');
    }
}
