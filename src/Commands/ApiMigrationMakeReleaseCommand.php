<?php

namespace LukePOLO\LaravelApiMigrations\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use LukePOLO\LaravelApiMigrations\ServiceProvider;

class ApiMigrationMakeReleaseCommand extends Command
{
    protected $release;
    protected $version;
    protected $apiDetails;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:api-release';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new API release';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        File::delete(ServiceProvider::REQUEST_MIGRATIONS_CACHE);

        $this->apiDetails = app()->make('getApiDetails');

        $this->version = $this->choice(
            'Which API version would you like to publish to?',
            $choices = $this->publishableApiVersions()
        );

        if ($this->version == $choices[0]) {
            $this->version = $this->ask('Please enter a version number :', $this->apiDetails->keys()->count() + 1);
        }

        $this->version = str_replace('V', '', $this->version);

        if (! is_numeric($this->version)) {
            $this->error('You provided a invalid version number');

            return false;
        }

        $this->release = $this->ask('Please enter your release in YYYY-MM-DD format :', Carbon::now()->format('Y-m-d'));

        if (! preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', str_replace('_', '-', $this->release))) {
            $this->error('You provided a invalid date');

            return false;
        }

        File::makeDirectory($this->getPath(), 493, true, true);
        File::put($this->getPath().'/.gitkeep', '');
    }

    /**
     * The choices available via the prompt.
     *
     * @return array
     */
    protected function publishableApiVersions()
    {
        return array_merge(
            ['<comment>Create New Version</comment>'],
            $this->apiDetails->keys()->toArray()
        );
    }

    protected function getPath()
    {
        return str_replace('\\', '/', $this->laravel->getNamespace().config('api-migrations.path').'/V'.$this->version.'/Release_'.str_replace('-', '_', $this->release));
    }
}
