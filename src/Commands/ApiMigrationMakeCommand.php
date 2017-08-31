<?php

namespace LukePOLO\LaravelApiMigrations\Commands;

use Illuminate\Console\GeneratorCommand;

class ApiMigrationMakeCommand extends GeneratorCommand
{
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
    protected $description = 'Create a new api migration';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        parent::handle();
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
        return $rootNamespace . '\Http\Migrations';
    }
}
