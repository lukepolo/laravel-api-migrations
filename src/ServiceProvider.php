<?php

namespace LukePOLO\LaravelApiMigrations;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Finder\SplFileInfo;
use LukePOLO\LaravelApiMigrations\Commands\ApiMigrationMakeCommand;
use LukePOLO\LaravelApiMigrations\Commands\CacheRequestMigrationsCommand;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const REQUEST_MIGRATIONS_CACHE = '/bootstrap/cache/api-migrations.php';

    protected $migrationsPath;

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('api-migrations.php'),
            ], 'config');
        }

        if (! $this->migrationHasAlreadyBeenPublished()) {
            $this->publishes([
                __DIR__.'/database/migrations/add_api_version_to_users_table.php.stub' => database_path('migrations/'.date('Y_m_d_His').'_add_api_version_to_users_table.php'),
            ], 'migrations');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'api-migrations');

        $this->migrationsPath = app_path('Http/ApiMigrations');

        $this->commands([
            ApiMigrationMakeCommand::class,
            CacheRequestMigrationsCommand::class,
        ]);

        $this->app->singleton(Migrator::class, function () {
            return new Migrator(Config::get('api-migrations'));
        });

        $this->app->alias(Migrator::class, 'api-migrations');

        $this->app->bind('getApiMigrations', function () {
            return $this->generateApiDetails();
        });

        $cacheFile = base_path(self::REQUEST_MIGRATIONS_CACHE);

        if (File::exists($cacheFile)) {
            return collect(require($cacheFile));
        }
    }

    protected function generateApiDetails()
    {
        if (File::exists($this->migrationsPath)) {
            return $this->getApiVersions()
                ->mapWithKeys(function ($version) {
                    return [
                        $version => $this->getApiVersionReleases($version),
                    ];
                });
        }

        return collect();
    }

    protected function getApiVersions()
    {
        return collect(File::directories($this->migrationsPath))
            ->map(function ($versionDirectory) {
                return substr($versionDirectory, strpos($versionDirectory, 'V') + 1);
            });
    }

    protected function getApiVersionReleases($version)
    {
        $migrationPath = $this->migrationsPath.'/V'.$version;
        if (File::exists($migrationPath)) {
            return collect(File::directories($migrationPath))
                ->map(function ($release) {
                    return substr($release, strpos($release, 'Release_') + 8);
                })
                ->mapWithKeys(function ($release) use ($migrationPath) {
                    return [
                        $release => $this->getApiReleaseMigrations($migrationPath.'/Release_'.$release),
                    ];
                });
        }
    }

    protected function getApiReleaseMigrations($release)
    {
        $files = collect();

        foreach (File::files($release) as $file) {
            $files->push(
                $this->convertToNamespace($file)
            );
        }

        return $files;
    }

    /**
     * Checks to see if the migration has already been published.
     *
     * @return bool
     */
    protected function migrationHasAlreadyBeenPublished()
    {
        return count(
                glob(
                    database_path('migrations/*_add_api_version_to_users_table.php')
                )
            ) > 0;
    }

    /**
     * @param SplFileInfo $file
     * @return string
     */
    protected function convertToNamespace(SplFileInfo $file)
    {
        return
            $this->app->getNamespace().
            str_replace(
            '/',
            '\\',
            str_replace(
                app_path().'/',
                '',
                $file->getPath()
            ).'/'.$file->getBasename('.php')
        );
    }
}
