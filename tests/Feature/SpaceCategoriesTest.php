<?php

namespace Tests\Feature;

use Tests\ParentTestClass;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\Space;

class SpaceCategoriesTest extends ParentTestClass
{

    use WithoutMiddleware;

	public function setUp(): void
    {
        parent::setUp();
        $this->space = factory(Space::class)->create();
    }
    
    public function testRouteReponse()
    {
        $space_id = $this->space->id;
        $response = $this->get('space-categories/'.$space_id);
        $response->assertStatus(200);
    }
}