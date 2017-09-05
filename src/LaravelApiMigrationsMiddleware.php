<?php

namespace LukePOLO\LaravelApiMigrations;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use LukePOLO\LaravelApiMigrations\Traits\ApiRequestHeadersTrait;

class LaravelApiMigrationsMiddleware
{
    use ApiRequestHeadersTrait;

    /** @var \Illuminate\Http\Request */
    protected $request;

    /** @var Migrator */
    protected $migrator;

    protected $releases;
    protected $apiDetails;
    protected $currentVersion;

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next) : Response
    {
        $this->request = $request;
        $this->apiDetails = app()->make('getApiDetails');

        $this->currentVersion = $this->getCurrentVersion();

        /* @var Migrator $migrator */
        $this->migrator = new Migrator;

        $this->setupRequest()
            ->validateVersion();

        return $this->migrator->setRequest($request)
            ->setReleases($this->releases)
            ->processResponseMigrations(
                $next($this->migrator->processRequestMigrations())
            )->getResponse();
    }

    /**
     * @return $this
     */
    private function setupRequest() : LaravelApiMigrationsMiddleware
    {
        $user = $this->request->user();

        if (empty($this->getVersion())) {
            $this->setVersion($this->currentVersion);

            if ($user) {
                if (! empty($user->api_version)) {
                    $this->setVersion($user->api_version);
                } else {
                    if (! empty($this->currentVersion) && config('api-migrations.version_pinning')) {
                        $user->update([
                            'api_version' => $this->currentVersion,
                        ]);
                    }
                }
            }
        }

        $this->getReleases();

        return $this;
    }

    /**
     * @return Collection
     */
    public function getReleases() : Collection
    {
        if (! empty($this->releases)) {
            return $this->releases;
        }

        $apiVersions = $this->apiDetails->get($this->getApiVersion())->sortBy(function ($value, $key) {
            return $key;
        });

        return $this->releases = $apiVersions ? $apiVersions : collect();
    }

    private function validateVersion()
    {
        $version = $this->getVersion();

        if (
            $version &&
            $this->currentVersion !== $version &&
            ! $this->releases->keys()->contains($version)
        ) {
            throw new HttpException(400, 'The version is invalid');
        }
    }

    /**
     * @return string
     */
    protected function getApiVersion()
    {
        if ($this->request->route()) {
            $routePrefix = explode('/', $this->request->route()->getPrefix());

            if (isset($routePrefix[1])) {
                return strtoupper($routePrefix[1]);
            }
        }

        return $this->apiDetails->keys()->last();
    }

    protected function getCurrentVersion()
    {
        $currentVersion = config('api-migrations.current_versions.'.$this->getApiVersion());

        if (empty($currentVersion)) {
            $currentVersion = $this->getReleases()->keys()->last();
        }

        return $currentVersion;
    }
}
