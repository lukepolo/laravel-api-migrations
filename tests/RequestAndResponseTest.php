<?php

namespace LukePOLO\LaravelApiMigrations\Tests;

use Symfony\Component\HttpKernel\Exception\HttpException;

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
    public function it_will_get_an_unmodified_user_object_with_latest_version()
    {
        $response = $this->get(route('show-users'), [
            'Api-Version'  => '2017-09-31',
        ]);

        $response->assertJson([
            'id'   => 123,
            'name' => [
                'firstname' => 'Dwight',
                'lastname'  => 'Schrute',
            ],
        ]);
    }

    /** @test */
    public function it_will_get_a_modified_user_object_of_migrations_of_only_current_and_behind_version_given()
    {
        $response = $this->get(route('show-users'), [
            'Api-Version'  => '2017-08-31',
        ]);

        $response->assertJson([
            'id'        => 123,
            'firstname' => 'Dwight',
            'lastname'  => 'Schrute',
            'title'     => 'Assistant to the Regional Manager',
        ]);

        $response->assertHeader('Api-version', '2017-08-31');
    }

    /** @test */
    public function it_will_get_modified_user_object_of_multiple_migrations()
    {
        $response = $this->get(route('show-users'), [
            'Api-Version'  => '2017-08-01',
        ]);

        $response->assertJson([
            'id'        => 123,
            'firstname' => 'Dwight',
            'lastname'  => 'Schrute',
            'secret_title' => 'Assistant Regional Manager',
            'title'     => 'Assistant to the Regional Manager',
        ]);

        $response->assertHeader('Api-version', '2017-08-01');
    }

    /** @test */
    public function it_will_throw_an_exception_if_the_version_is_invalid()
    {
        $this->expectException(HttpException::class);

        $this->get(route('show-users'), [
            'Api-Version'  => '2016-03-03',
        ])->json();
    }

    /** @test */
    public function it_will_get_version_from_path()
    {
        $this->markTestIncomplete();
        $response = $this->get(route('show-users-v1'), [
            'Api-Version'  => '2017-08-31',
        ]);

        $response->assertJson([
            'id'        => 123,
            'firstname' => 'Dwight',
            'lastname'  => 'Schrute',
            'title'     => 'Assistant to the Regional Manager',
        ]);

        $response->assertHeader('Api-version', '2017-08-31');
    }
}
