<?php

namespace LukePOLO\LaravelApiMigrations\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use LukePOLO\LaravelApiMigrations\ServiceProvider;

class CacheRequestMigrationsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cache:api-migrations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Caches the API migrations for production';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $requestMigrationsCache = ServiceProvider::REQUEST_MIGRATIONS_CACHE;

        File::delete($requestMigrationsCache);

        File::put(
            base_path($requestMigrationsCache),
            '<?php return '.var_export(app()->make('getApiMigrations')->toArray(), true).';'
        );

        $this->info('Api Migrations Cached');
    }
}
