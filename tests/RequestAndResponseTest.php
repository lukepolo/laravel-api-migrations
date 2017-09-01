<?php

namespace LukePOLO\LaravelApiMigrations\Tests;

use Symfony\Component\HttpKernel\Exception\HttpException;
use LukePOLO\LaravelApiMigrations\Facades\LaravelApiMigrations;

class RequestAndResponseTest extends TestCase
{
    /** @test */
    public function it_will_get_an_unmodified_user_object()
    {
        $response = $this->get(route('show-users'));

        $response->assertJson([
            'id'   => 123,
            'name' => [
                'firstname' => 'Dwight',
                'lastname'  => 'Schrute',
            ],
        ]);
        
    }

    /** @test */
    public function it_will_get_a_modified_user_object()
    {
        $response = $this->get(route('show-users'), [
            'Api-Version'  => '2017-08-31',
        ]);

        $response->assertJson([
            'id'        => 123,
            'firstname' => 'Dwight',
            'lastname'  => 'Schrute',
        ]);

        $response->assertHeader('Api-version', '2017-08-31');
    }

    /** @test */
    public function it_will_throw_an_exception_if_the_version_is_invalid()
    {
        $this->expectException(HttpException::class);

        $this->get(route('show-users'), [
            'Api-Version'  => '2016-03-03',
        ])->json();
    }

}
