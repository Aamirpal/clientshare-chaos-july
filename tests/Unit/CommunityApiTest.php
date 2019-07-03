<?php

namespace Tests\Unit;

use Tests\ParentTestClass;
use App\Models\Space;
use App\Models\SpaceUser;
use App\Repositories\SpaceUser\SpaceUser as SpaceUserRepo;

class CommunityApiTest extends ParentTestClass
{
    public function setup(): void
    {
        parent::setUp();
        $this->space = factory(Space::class)->create();
    }

    public function testCommunityTile()
    {
        $space_user = new SpaceUserRepo(new SpaceUser());
        $response = $space_user->communityMember($this->space->id);
        $this->assertArrayHasKey('community_members', $response);
    }
}
