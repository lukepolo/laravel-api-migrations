<?php

namespace LukePOLO\LaravelApiMigrations;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class Migrator
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response;

    protected $releases;

    protected $currentVersion = null;

    protected $requestVersion = null;

    protected $responseVersion = null;

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
    public function setRequest(Request $request): Migrator
    {
        $this->request = $request;

        $this->requestVersion = $this->requestVersion ?: $request->header(config('api-migrations.headers.request-version'));
        $this->responseVersion = $this->responseVersion ?: $request->header(config('api-migrations.headers.response-version'));
        $this->responseVersion = $this->currentVersion ?: $request->header(config('api-migrations.headers.current-version'));

        return $this;
    }

    /**
     * @param $releases
     * @return Migrator
     */
    public function setReleases($releases): Migrator
    {
        $this->releases = $releases;

        return $this;
    }

    /**
     * Set both the response and request version.
     *
     * @param string $version
     * @return Migrator
     */
    public function setVersion(string $version) : Migrator
    {
        $this->requestVersion = $version;
        $this->responseVersion = $version;

        return $this;
    }

    /**
     * Process the migrations for the incoming request.
     *
     * @return \Illuminate\Http\Request
     */
    public function processRequestMigrations() : Request
    {
        $this->neededMigrations($this->requestVersion)
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
     * Process the migrations for the outgoing response.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return $this
     */
    public function processResponseMigrations(Response $response)
    {
        $this->response = $response;

        Collection::make($this->neededMigrations($this->responseVersion))
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
     * Set the API Response Headers.
     *
     * @return $this
     */
    private function setResponseHeaders()
    {
        $this->response->headers->set(config('api-versions.headers.current-version'), $this->currentVersion, true);
        $this->response->headers->set(config('api-versions.headers.request-version'), $this->requestVersion, true);
        $this->response->headers->set(config('api-versions.headers.response-version'), $this->responseVersion, true);

        return $this;
    }

    /**
     * Figure out which migrations apply to the current request.
     *
     * @param $migrationVersion string The migration version to check migrations against
     *
     * @return Collection
     */
    private function neededMigrations($migrationVersion) : Collection
    {
        if (empty($migrationVersion)) {
            return collect();
        }

        return collect($this->releases)
            ->reject(function ($version) use ($migrationVersion) {
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
