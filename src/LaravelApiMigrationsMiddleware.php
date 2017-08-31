<?php

namespace LukePOLO\LaravelApiMigrations;

use Closure;
use function strtoupper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LaravelApiMigrationsMiddleware
{
    /** @var \Illuminate\Http\Request */
    protected $request;

    /** @var  Migrator */
    protected $migrator;

    protected $releases;

    protected $currentVersion;
    protected $requestVersion;
    protected $responseVersion;

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next) : Response
    {
        /** @var Migrator $migrator */
        $this->migrator = Container::getInstance()
            ->make(Migrator::class);

        $this->setupRequest($request)
            ->validateRequest();

        return $this->migrator->setRequest($request)
            ->setReleases($this->releases)
            ->processResponseMigrations(
                $next($this->migrator->processRequestMigrations())
            )->getResponse();
    }

    /**
     * @param Request $request
     *
     * @return $this
     */
    private function setupRequest(Request $request)
    {
        $this->request = $request;
        $this->currentVersion = $this->cleanVersion(config('api-migrations.current_versions.'.$this->getApiVersion()));

        $user = $this->request->user();

        if ($user && !empty($user->api_version)) {
            $this->migrator->setVersion($user->api_version);
        }

        $this->setResponseVersion($this->responseVersion);
        $this->setRequestVersion($this->requestVersion);

        $this->releases = $this->releases();

        return $this;
    }

    /**
     * Get all the available releases.
     *
     * @return Collection
     */
    private function releases() : Collection
    {
        if ($this->releases) {
            return $this->releases;
        }

        $apiVersions = app()->make('getApiDetails')->get($this->getApiVersion());

        return $apiVersions ? $apiVersions : collect();
    }

    /**
     *
     */
    private function validateRequest()
    {
        $this->validateRequestVersion();
        $this->validateResponseVersion();
    }

    /**
     *
     */
    private function validateRequestVersion()
    {
        $requestVersion = $this->getRequestVersion();

        if (
            $requestVersion &&
            $requestVersion < $this->currentVersion &&
            ! $this->releases->keys()->contains($requestVersion)
        ) {
            throw new HttpException(400, 'The request version is invalid');
        }
    }

    /**
     * Get the request version from the request.
     *
     * @return string
     */
    private function getRequestVersion() : string
    {
        return $this->cleanVersion(
            $this->request->header(config('api-migrations.headers.request-version'))
        );
    }

    /**
     *
     */
    private function validateResponseVersion()
    {
        $responseVersion = $this->getResponseVersion();

        if (
            $responseVersion &&
            $responseVersion < $this->currentVersion &&
            ! $this->releases->keys()->contains($responseVersion)
        ) {
            throw new HttpException(400, 'The response version is invalid');
        }
    }

    /**
     * Get the response version from the request.
     *
     * @return string
     */
    private function getResponseVersion() : string
    {
        return $this->cleanVersion(
            $this->request->header(config('api-migrations.headers.response-version'), '')
        );
    }

    /**
     * @param string $requestVersion
     */
    private function setRequestVersion($requestVersion = '')
    {
        $this->request->headers->set(config('api-migrations.headers.request-version'), $requestVersion);
    }


    /**
     * @param string $responseVersion
     */
    private function setResponseVersion($responseVersion = '')
    {
        $this->request->headers->set(config('api-migrations.headers.response-version'), $responseVersion);
    }

    /**
     * @param $version
     * @return mixed
     */
    private function cleanVersion($version)
    {
        return str_replace('-', '_', $version);
    }

    /**
     * @return mixed
     */
    protected function getApiVersion()
    {
        $routePrefix = explode('/', $this->request->route()->getPrefix());

        if(isset($routePrefix[1])) {
            return strtoupper($routePrefix[1]);
        }

        dd('WE NEED TO GRAB THE LATEST VERSION FROM THE VERSIONS');
    }

}
