<?php

namespace LukePOLO\LaravelApiMigrations;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use LukePOLO\LaravelApiMigrations\Traits\ApiRequestHeadersTrait;

class Migrator
{
    use ApiRequestHeadersTrait;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response;

    protected $releases;

    /**
     * @var array
     */
    protected $config;

    /**
     * Set the request from the request headers.
     *
     * @param Request $request
     * @return Migrator
     */
    public function setRequest(Request $request) : Migrator
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @param Collection $releases
     *
     * @return Migrator
     */
    public function setReleases(Collection $releases) : Migrator
    {
        $this->releases = $releases;

        return $this;
    }

    /**
     * @return \Illuminate\Http\Request
     */
    public function processRequestMigrations() : Request
    {
        $this->neededMigrations($this->getRequestVersion())
            ->transform(function ($migrations) {
                return Collection::make($migrations)->flatten();
            })
            ->flatten()
            ->each(function ($migration) {
                $this->request = (new $migration())->migrateRequest($this->request);
            });

        return $this->request;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return Migrator
     */
    public function processResponseMigrations(Response $response) : Migrator
    {
        $this->response = $response;

        Collection::make($this->neededMigrations($this->getResponseVersion()))
            ->reverse()
            ->transform(function ($migrations) {
                return Collection::make($migrations);
            })
            ->flatten()
            ->each(function ($migration) {
                $this->response = (new $migration())->migrateResponse($this->response);
            });

        return $this;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        $this->setResponseHeaders();

        return $this->response;
    }

    /**
     * @return Migrator
     */
    private function setResponseHeaders() : Migrator
    {
        $this->setCurrentVersion($this->getCurrentVersion());
        $this->setRequestVersion($this->getRequestVersion());
        $this->setResponseVersion($this->getResponseVersion());

        return $this;
    }

    /**
     * @param $migrationVersion string The migration version to check migrations against
     *
     * @return Collection
     */
    private function neededMigrations($migrationVersion) : Collection
    {
        if (empty($migrationVersion)) {
            return collect();
        }

        return $this->releases
            ->reject(function ($index, $version) use ($migrationVersion) {
                return $version < $migrationVersion;
            })
            ->filter(function ($classList) {
                return Collection::make($classList)->transform(function ($class) {
                    return Collection::make((new $class)->paths())->filter(function ($path) {
                        return $this->request->fullUrlIs($path);
                    });
                })->isNotEmpty();
            });
    }
}
