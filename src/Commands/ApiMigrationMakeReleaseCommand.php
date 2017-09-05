<?php

namespace LukePOLO\LaravelApiMigrations\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Console\GeneratorCommand;
use LukePOLO\LaravelApiMigrations\ServiceProvider;

class ApiMigrationMakeReleaseCommand extends GeneratorCommand
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

        $this->release = $this->choice(
            'Select a release',
            $choices = $this->publishableApiVersionReleases($this->version)
        );

        if ($this->release == $choices[0]) {
            $this->release = $this->ask('Please enter your release in YYYY-MM-DD format :', Carbon::now()->format('Y-m-d'));
        }

        if (! preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', str_replace('_', '-', $this->release))) {
            $this->error('You provided a invalid date');

            return false;
        }

        parent::handle();
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

    protected function publishableApiVersionReleases(int $version)
    {
        $release = $this->apiDetails->get('V'.$version);

        return array_merge(
            ['<comment>Create New Release</comment>'],
            $release ? $release->keys()->toArray() : []
        );
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/.gitkeep.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\ApiMigrations\V'.$this->version.'\Release_'.str_replace('-', '_', $this->release);
    }
}
