<?php

namespace LukePOLO\LaravelApiMigrations\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Console\GeneratorCommand;
use LukePOLO\LaravelApiMigrations\ServiceProvider;

class ApiMigrationMakeCommand extends GeneratorCommand
{
    protected $version;
    protected $versions;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:api-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new request migration';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        File::delete(ServiceProvider::REQUEST_MIGRATIONS_CACHE);

        $this->versions = app()->make('getRequestMigrationsVersions')->keys();

        $this->version = $this->choice(
            "Which version would you like to publish to?",
            $choices = $this->publishableChoices()
        );

        if ($this->version == $choices[0]) {
            $this->version = $this->ask('Please enter your version in Y-m-d format.', Carbon::now()->format('Y-m-d'));
        }

        if(empty(config('request-migrations.current_version'))) {
            $this->info('Please set your default version in your request-migrations config');
        }

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",str_replace('_', '-', $this->version))) {
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
    protected function publishableChoices()
    {
        if($this->versions) {
            return array_merge(
                ['<comment>Create New Version</comment>'],
                $this->versions->toArray()
            );
        }

    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../../stubs/migration.stub';
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
        return $rootNamespace . '\Http\Migrations\Version_'.str_replace('-', '_', $this->version);
    }
}