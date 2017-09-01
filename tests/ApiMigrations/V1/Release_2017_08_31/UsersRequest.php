<?php

namespace App\Http\ApiMigrations\V1\Release_2017_08_31;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use LukePOLO\LaravelApiMigrations\ApiMigration;

class UsersRequest extends ApiMigration
{
    /**
     * Migrate the request for the application to "read".
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Request
     */
    public function migrateRequest(Request $request) : Request
    {
        return $request;
    }

    /**
     * Migrate the response to display to the client.
     *
     * @param \Illuminate\Http\Response $response
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function migrateResponse(Response $response) : Response
    {
        $content = $response->original;

        $content['firstname'] = array_get($content, 'name.firstname');
        $content['lastname'] = array_get($content, 'name.lastname');

        unset($content['name']);

        return response()->json($content);
    }

    /**
     * Define which named paths should this migration modify.
     *
     * @return array
     */
    public function paths() : array
    {
        return [
            route('show-users'),
        ];
    }
}
