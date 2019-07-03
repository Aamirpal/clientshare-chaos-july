<?php

namespace Tests\Feature;

use Tests\ParentTestClass;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\SpaceUser;

class CommunityApiTest extends ParentTestClass
{

    use WithoutMiddleware;

	public function setUp(): void
    {
        parent::setUp();
        $this->space_user = factory(SpaceUser::class)->create();
    }
    
    public function testRouteReponse()
    {
        $response = $this->get('community-members/'.$this->space_user->space_id);
        $response->assertStatus(200);
    }
}
