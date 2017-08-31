<?php

namespace LukePOLO\LaravelApiMigrations;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Container\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LaravelApiMigrationsMiddleware
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;
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
        $this->request = $request;
        $this->releases = $this->releases();
        $this->currentVersion = $this->cleanVersion(config('api-migrations.current_versions.'.$this->getApiVersion()));

        $this->setupRequest();

        /** @var Migrator $migrator */
        $migrator = Container::getInstance()
            ->make(Migrator::class)
            ->setRequest($request)
            ->setReleases($this->releases)
            ->setCurrentVersion($this->currentVersion);

        return $migrator->processResponseMigrations(
                $next($migrator->processRequestMigrations())
            )
            ->setResponseHeaders()
            ->getResponse();
    }

    private function setupRequest()
    {
        $user = $this->request->user();

        if ($user && $user->api_version) {
            $this->responseVersion = $user->api_version;
            $this->requestVersion = $user->api_version;
        }

        $this->setResponseVersion($this->responseVersion);
        $this->setRequestVersion($this->requestVersion);
        $this->validateRequest();
    }

    /**
     * Get all the available releases.
     *
     * @return array
     */
    private function releases() : array
    {
        if ($this->releases) {
            return $this->releases;
        }

        $apiVersions = app()->make('getApiDetails')->get($this->getApiVersion());

        return $apiVersions ? $apiVersions->keys()->toArray() : [];
    }

    private function validateRequest()
    {
        $this->validateRequestVersion();
        $this->validateResponseVersion();
    }

    private function validateRequestVersion()
    {
        $requestVersion = $this->requestVersion();

        if (
            $requestVersion &&
            $requestVersion < $this->currentVersion &&
            ! in_array($requestVersion, $this->releases())
        ) {
            throw new HttpException(400, 'The request version is invalid');
        }
    }

    /**
     * Get the request version from the request.
     *
     * @return string
     */
    private function requestVersion() : string
    {
        return $this->cleanVersion(
            $this->request->header(config('api-migrations.headers.request-version'), '')
        );
    }

    private function validateResponseVersion()
    {
        $responseVersion = $this->responseVersion();

        if (
            $responseVersion &&
            $responseVersion < $this->currentVersion &&
            ! in_array($responseVersion, $this->releases())
        ) {
            throw new HttpException(400, 'The response version is invalid');
        }
    }

    /**
     * Get the response version from the request.
     *
     * @return string
     */
    private function responseVersion() : string
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

        return strtoupper($routePrefix[1]);
    }
}
