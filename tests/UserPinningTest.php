<?php

namespace LukePOLO\LaravelApiMigrations\Tests;

use Illuminate\Support\Facades\Config;
use LukePOLO\LaravelApiMigrations\Tests\Models\User;

class UserPinningTest extends TestCase
{
    /** @test */
    public function it_will_pin_version_to_user()
    {
        Config::set('api-migrations.version_pinning', true);

        $user = new User();

        $response = $this
            ->actingAs($user)
            ->get(route('show-users'));

        $response->assertJson([
            'id'   => 123,
            'name' => [
                'firstname' => 'Dwight',
                'lastname'  => 'Schrute',
            ],
        ]);

        $this->assertEquals('2017_09_31', $user->api_version);
    }

    /** @test */
    public function it_will_pin_version_to_user_and_use_on_next_request()
    {
        Config::set('api-migrations.version_pinning', true);

        $user = new User();

        $user->api_version = '2017-08-31';

        $response = $this
            ->actingAs($user)
            ->get(route('show-users'));

        $response->assertJson([
            'id'        => 123,
            'firstname' => 'Dwight',
            'lastname'  => 'Schrute',
            'title'     => 'Assistant to the Regional Manager',
        ]);

        $response->assertHeader('Api-version', '2017-08-31');
    }
}
