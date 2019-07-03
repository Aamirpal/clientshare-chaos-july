<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\{SpaceUser, User};

class TwitterTest extends TestCase
{
    use WithFaker;
    use WithoutMiddleware;

    public function setUp(): void 
    {
        parent::setUp();
        $this->space_user = factory(SpaceUser::class)->create();
    }

    public function testAddTwitterHandles()
    {
        $data['space_id'] = $this->space_user->space_id;
        $data['user_id'] = $this->space_user->user_id;
        $no_of_twitter = rand(1,3);
        for ($i=1; $i <= $no_of_twitter ; $i++) { 
            $data['twitter_handles'][] =  '@'.$this->faker->word();
        }
        $this->be(User::find($data['user_id']));
        $response = $this->post('/save-twitter-handler',$data);
        $response->assertStatus(200);
    }

    public function testGetTwitterHandles()
    {
        $this->be(User::find($this->space_user->user_id));
        $response = $this->get('/get-twitter-handler/'.$this->space_user->space_id);
        $response->assertStatus(200);
    }
}
