<?php

namespace LukePOLO\LaravelApiMigrations;

use Illuminate\Support\Facades\File;
use LukePOLO\LaravelApiMigrations\Commands\ClearCacheRequestMigrationsCommand;
use Symfony\Component\Finder\SplFileInfo;
use LukePOLO\LaravelApiMigrations\Commands\ApiMigrationMakeCommand;
use LukePOLO\LaravelApiMigrations\Commands\CacheRequestMigrationsCommand;
use LukePOLO\LaravelApiMigrations\Commands\ApiMigrationMakeReleaseCommand;

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

        $this->commands([
            ApiMigrationMakeCommand::class,
            CacheRequestMigrationsCommand::class,
            ApiMigrationMakeReleaseCommand::class,
            ClearCacheRequestMigrationsCommand::class,
        ]);

        $this->app->singleton(Migrator::class, function () {
            return new Migrator;
        });

        $this->app->alias(Migrator::class, 'laravel-api-migrations');

        $this->app->singleton('getApiDetails', function () {
            return $this->generateApiDetails();
        });
    }

    /**
     * @return \Illuminate\Support\Collection|static
     */
    protected function generateApiDetails()
    {
        $this->migrationsPath = app_path(config('api-migrations.path'));

        $cacheFile = base_path(self::REQUEST_MIGRATIONS_CACHE);

        if (File::exists($cacheFile)) {
            return collect(require($cacheFile))->map(function ($files) {
                return collect($files)->map(function ($releases) {
                    return collect($releases);
                });
            });
        }

        return $this->getApiVersions()
            ->mapWithKeys(function ($version) {
                return [
                    $version => $this->getApiVersionReleases($version),
                ];
            });
    }

    /**
     * @return static
     */
    protected function getApiVersions()
    {
        if (File::exists($this->migrationsPath)) {
            return collect(File::directories($this->migrationsPath))
                ->map(function ($versionDirectory) {
                    return substr($versionDirectory, strpos($versionDirectory, 'V'));
                });
        }

        return collect();
    }

    /**
     * @param $version
     * @return static
     */
    protected function getApiVersionReleases($version)
    {
        $migrationPath = $this->migrationsPath.'/'.$version;

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

    /**
     * @param $release
     * @return \Illuminate\Support\Collection
     */
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
