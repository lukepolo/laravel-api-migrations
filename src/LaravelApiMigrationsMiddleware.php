<?php

namespace LukePOLO\LaravelApiMigrations;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
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

        /* @var Migrator $migrator */
        $this->migrator = Container::getInstance()
            ->make(Migrator::class);

        $this->setupRequest()
            ->validateRequest();

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
        $currentVersion = config('api-migrations.current_versions.'.$this->getApiVersion());

        $this->setCurrentVersion($currentVersion);

        $user = $this->request->user();

        if ($user) {
            if (! empty($user->api_version)) {
                $this->setVersion($user->api_version);
            } else {
                if (! empty($currentVersion) && config('api-migrations.version_pinning')) {
                    $user->update([
                        'api_version' => $currentVersion,
                    ]);
                    $this->setVersion($currentVersion);
                }
            }
        }

        $this->releases = $this->releases();

        return $this;
    }

    /**
     * @return Collection
     */
    private function releases() : Collection
    {
        if ($this->releases) {
            return $this->releases;
        }

        $apiVersions = $this->apiDetails->get($this->getApiVersion());

        return $apiVersions ? $apiVersions : collect();
    }

    private function validateRequest()
    {
        $this->validateRequestVersion();
        $this->validateResponseVersion();
    }

    private function validateRequestVersion()
    {
        $requestVersion = $this->getRequestVersion();

        if (
            $requestVersion &&
            $requestVersion < $this->getCurrentVersion() &&
            ! $this->releases->keys()->contains($requestVersion)
        ) {
            throw new HttpException(400, 'The request version is invalid');
        }
    }

    private function validateResponseVersion()
    {
        $responseVersion = $this->getResponseVersion();

        if (
            $responseVersion &&
            $responseVersion < $this->getCurrentVersion() &&
            ! $this->releases->keys()->contains($responseVersion)
        ) {
            throw new HttpException(400, 'The response version is invalid');
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
}
