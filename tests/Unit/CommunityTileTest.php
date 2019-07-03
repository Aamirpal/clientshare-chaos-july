<?php

namespace Tests\Unit;

use Tests\ParentTestClass;
use App\Models\Space;
use App\Models\SpaceUser;
use App\Repositories\SpaceUser\SpaceUser as SpaceUserRepo;

class CommunityTileTest extends ParentTestClass
{
	public function setup(): void
    {
        parent::setUp();
        $this->space = factory(Space::class)->create();
    }

    public function testCommunityTile()
    {
        $space_user = new SpaceUserRepo(new SpaceUser());
        $response = $space_user->communityTile($this->space->id);
        $this->assertArrayHasKey('users_count', $response);
    }
}
