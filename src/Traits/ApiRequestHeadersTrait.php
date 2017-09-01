<?php

namespace LukePOLO\LaravelApiMigrations\Traits;

trait ApiRequestHeadersTrait
{
    /**
     * Get the request version from the request.
     *
     * @param bool $clean
     * @return string
     */
    private function getCurrentVersion($clean = true)
    {
        $version = $this->request->header(config('api-migrations.headers.current-version'));

        if($clean) {
            return $this->cleanVersion($version);
        }

        return $version;
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
     * @param bool $clean
     * @return string
     */
    private function getRequestVersion($clean = true) : string
    {
        $version = $this->request->header(config('api-migrations.headers.request-version'));

        if($clean) {
            return $this->cleanVersion($version);
        }

        return $version;
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
    private function getResponseVersion($clean = true) : string
    {
        $version = $this->request->header(config('api-migrations.headers.response-version'), '');

        if($clean) {
            return $this->cleanVersion($version);
        }

        return $version;
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
