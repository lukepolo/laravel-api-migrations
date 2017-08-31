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
    protected $name = 'cache:request-migrations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Caches the request migrations for production';

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
            '<?php return '.var_export(app()->make('getRequestMigrationsVersions')->toArray(), true).';'
        );

        $this->info('Request Migrations Cached');
    }
}
