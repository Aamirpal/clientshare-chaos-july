<?php

namespace Tests\Unit;

use Tests\ParentTestClass;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Repositories\SpaceCategory\SpaceCategoryRepository;
use App\Models\Space;

class SpaceCategoriesTest extends ParentTestClass
{

    use WithoutMiddleware;

	public function setUp(): void
    {
        parent::setUp();
        $this->space = factory(Space::class)->create();
    }
    
    public function testGetSpaceCategories()
    {
        $space_category = new SpaceCategoryRepository(new Space());
        $response = $space_category->getSpaceCategories($this->space->id);
        $response = is_array($response)? true :false;
        $this->assertTrue($response);
    }
}