<?php

namespace Tests\Unit;

use Tests\ParentTestClass;
use App\Models\Space;
use App\Repositories\Space\SpaceRepository;

class CommunitySpaceInfoTest extends ParentTestClass
{
    public function setup(): void
    {
        parent::setUp();
        $this->space = factory(Space::class)->create();
    }

    public function testCommunityTile()
    {
        $space = new SpaceRepository(new Space());
        $response = $space->communitySpaceInfo($this->space->id);
        $this->assertArrayHasKey('id', $response);
    }
}
