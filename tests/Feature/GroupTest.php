<?php

namespace Tests\Feature;

use Tests\ParentTestClass;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Repositories\GroupUser\GroupUserRepository;
use App\Models\{Space, Group, User, GroupUser, SpaceUser as SpaceUserModel};
use Illuminate\Foundation\Testing\WithFaker;
use App\Repositories\Group\GroupRepository;
use App\Repositories\SpaceUser\SpaceUser;
use Illuminate\Http\Request;


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
        $this->user = factory(User::class)->create();
    }

    public function testGroupDelete()
    {
        $group_user = factory(GroupUser::class)->create();
        $response = $this->delete('/group-delete/'.$group_user->group_id);
        $response->assertStatus(200);
    }

    public function testGroupDeleteWhenNoUsers()
    {
        $response = $this->delete('/group-delete/'.$this->group->id);
        $response->assertStatus(200);
    }

    public function testSearchUserApi() {
        $this->be($this->user);
        $key_word = $this->faker->word();
        $response = $this->get('search-space-users/'.$this->space->id.'/'.$key_word);
        $response->assertStatus(200);
        $response = $this->get('search-space-users/'.$this->space->id);
        $response->assertStatus(200);
    }

    public function testCreateGroupFunction() {
        $users = factory(User::class, 2)->create();
        $this->be($this->user);
        $test_data = [
            'name' => $this->faker->unique()->word,
            'space_id' => $this->space->id,
            'user_ids' => $users->pluck('id')->toArray()
        ];
        $response = $this->post(route('groups.create_group'), $test_data)
            ->assertStatus(200);
    }

    public function testGetUserGroupsApi() {
        $this->be($this->user);
        $response = $this->get(route('user_groups', [$this->space->id, $this->user->id]));
        $response->assertStatus(200);
    }
    public function testUpdateGroupApi() {
        $users = factory(User::class, 2)->create();
        $this->be($this->user);
        $test_data = [
            'name' => $this->faker->unique()->word,
            'space_id' => $this->space->id,
            'group_id' => $this->group->id,
            'user_ids' => $users->pluck('id')->toArray()
        ];
        $response = $this->post(route('groups.update_group'), $test_data)
            ->assertStatus(200);
    }

}
