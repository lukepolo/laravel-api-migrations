<?php

namespace LukePOLO\LaravelApiMigrations\Traits;

trait ApiRequestHeadersTrait
{
    /**
     * Get the request version from the request.
     *
     * @return string
     */
    private function getCurrentVersion() : string
    {
        return $this->cleanVersion(
            $this->request->header(config('api-migrations.headers.current-version'))
        );
    }

    /**
     * @param string $requestVersion
     */
    private function setCurrentVersion($requestVersion)
    {
        $this->request->headers->set(
            config('api-migrations.headers.current-version'),
            $requestVersion
        );
    }

    /**
     * @return string
     */
    private function getRequestVersion() : string
    {
        return $this->cleanVersion(
            $this->request->header(config('api-migrations.headers.request-version'))
        );
    }

    /**
     * @param string $requestVersion
     */
    private function setRequestVersion($requestVersion)
    {
        $this->request->headers->set(
            config('api-migrations.headers.request-version'),
            $requestVersion
        );
    }

    /**
     * @return string
     */
    private function getResponseVersion() : string
    {
        return $this->cleanVersion(
            $this->request->header(config('api-migrations.headers.response-version'), '')
        );
    }

    /**
     * @param string $responseVersion
     */
    private function setResponseVersion($responseVersion)
    {
        $this->request->headers->set(
            config('api-migrations.headers.response-version'),
            $responseVersion
        );
    }

    /**
     * @param string $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->setRequestVersion($version);
        $this->setResponseVersion($version);

        return $this;
    }

    /**
     * @param string $version
     * @return mixed
     */
    private function cleanVersion($version)
    {
        return str_replace('-', '_', $version);
    }

}