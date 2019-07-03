<?php

namespace Tests\Feature;

use Tests\ParentTestClass;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\{SpaceUser, User};


class SettingTest extends ParentTestClass {

    /**
     * A basic test example.
     *
     * @return void
     */
    use WithoutMiddleware;

    public function setUp(): void {
        parent::setUp();
        $this->space_user = factory(SpaceUser::class)->create();
    }

    public function testUserManagementTest()
    {
        $response = $this->actingAs(User::find($this->space_user->user_id))
            ->get('/user_management?space_id='. $this->space_user->space_id);
        $response->assertStatus(200);
    } 
}
