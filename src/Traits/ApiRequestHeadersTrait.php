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
    private function getVersion($clean = true)
    {
        $version = $this->request->header(config('api-migrations.headers.api-version'));

        if ($clean) {
            return $this->cleanVersion($version);
        }

        return $version;
    }

    /**
     * @param string $requestVersion
     */
    private function setVersion($requestVersion)
    {
        $this->request->headers->set(config('api-migrations.headers.api-version'), $requestVersion);
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
