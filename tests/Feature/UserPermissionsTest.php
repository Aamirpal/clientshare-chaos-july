<?php

namespace Tests\Feature;

use Tests\ParentTestClass;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\SpaceUser;

class UserPermissionsTest extends ParentTestClass {

    use WithoutMiddleware;

    public function setUp(): void {
        parent::setUp();
        $this->space_user = factory(SpaceUser::class)->create();
    }

    public function testUserPermissionsApi() {
        $response = $this->get('get-user-groups/' . $this->space_user->space_id . '/' . $this->space_user->user_id);
        $response->assertStatus(200);
    }
}
