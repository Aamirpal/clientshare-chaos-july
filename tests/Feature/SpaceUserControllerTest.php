<?php

namespace Tests\Feature;

use Tests\ParentTestClass;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\{Post, Space};

class SpaceUserControllerTest extends ParentTestClass
{
    use WithoutMiddleware;

    public function setUp(): void
    {
        parent::setUp();
        $this->space = factory(Space::class)->create();
        $this->logged_in_user = factory(\App\Models\User::class)->create();
    }

    public function testUserInformation()
    {
        $response = $this->actingAs($this->logged_in_user)
            ->get('user_information/'.$this->space->id.'/'.$this->logged_in_user->id);
        $response->assertSeeText('data');
        $response->assertStatus(200);
    }
}
