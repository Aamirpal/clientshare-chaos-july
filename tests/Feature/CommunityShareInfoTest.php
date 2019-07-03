<?php

namespace Tests\Feature;

use Tests\ParentTestClass;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\Space;

class CommunityShareInfoTest extends ParentTestClass
{
    use WithoutMiddleware;

	public function setUp(): void
    {
        parent::setUp();
        $this->space = factory(Space::class)->create();
    }
    
    public function testRouteReponse()
    {
        $response = $this->get('community-space-info/'.$this->space->id);
        $response->assertStatus(200);
    }
}
