<?php

namespace Tests\Unit;

use Tests\ParentTestClass;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\WithFaker;
use App\Repositories\SpaceUser\SpaceUser;
use App\Models\SpaceUser as SpaceUserModel;
use App\Repositories\Group\GroupRepository;
use App\Repositories\GroupUser\GroupUserRepository;
use Illuminate\Http\Request;
use App\Models\{Space, Group, User, GroupUser};

class GroupTest extends ParentTestClass {

    /**
     * A basic test example.
     *
     * @return void
     */
    use WithoutMiddleware;
    use WithFaker;

    public function setUp(): void {
        parent::setUp();
        $this->space = factory(Space::class)->create();
        $this->group = factory(Group::class)->create();
        $this->user = factory(user::class)->create();
    }

    public function testSearchUserFunction() {
        
        $this->be($this->user);
        $key_word = $this->faker->word();
        $space_user = new SpaceUser(new SpaceUserModel());
        $response = $space_user->searchSpaceUser($this->space->id,$key_word);
        $response = is_array($response)? true :false;
        $this->assertTrue($response);
    }

    public function testcreateGroupFunction(){
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $this->be($this->user);
        $test_data = [
            'name' => $this->faker->unique()->word,
            'space_id' => $this->space->id,
            'user_ids' => [$user1->id,$user2->id]
        ];
        $request = new Request($test_data);
        $group = new GroupRepository(new Group());
        $response = $group->createGroup($request);
        $this->assertArrayHasKey('name', $response);
    }

    public function testgroupListMethod()
    {
        $this->be($this->user);
        $group = new GroupUserRepository(new GroupUser());
        $space_user = new SpaceUser(new SpaceUserModel());
        $user_type = $space_user->checkUserBuyerOrSeller($this->group->space_id, $this->user->id);
        $response = $group->getUserGroups($this->group->space_id, $this->user->id, $user_type);
        $response = is_object($response)? true :false;
        $this->assertTrue($response);
    }

    public function testGroupDeleteFunction(){
        $group = new GroupRepository(new Group());
        $response = $group->deleteGroup($this->group->id);
        $this->assertTrue($response);
    }

}
