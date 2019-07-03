<?php
namespace Tests\Feature;

use Tests\ParentTestClass;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\{Post, Space};

class PostControllerTest extends ParentTestClass
{
	use WithoutMiddleware;

    public function setUp(): void
    {
        parent::setUp();
        $this->space = factory(Space::class)->create();
        $this->post = factory(Post::class)->create(['space_id' => $this->space->id]);
        $this->logged_in_user = factory(\App\Models\User::class)->create();
    }

    // public function testGetPosts()
    // {
    // 	$response = $this->get('get-posts/?space_id='.$this->space->id.'&space_category_id='.rand(1, 10));
    // 	$response->assertSeeText('posts');
    // 	$response->assertSeeText('offset');
    //     $response->assertStatus(200);
    // }

    public function testGetPost()
    {
        $response = $this->actingAs($this->logged_in_user)
            ->get('get-post/'.$this->space->id.'/'.$this->post->id);
        $response->assertSeeText('data');
        $response->assertSeeText('id');
        $response->assertStatus(200);
    }

    public function testPinPost()
    {
        $response = $this->get('pin-post/'.$this->space->id.'/'.$this->post->id.'/0');
        $response->assertSeeText('data');
        $response->assertSeeText('id');
        $response->assertStatus(200);
    }

    public function testDeletePost()
    {
        $response = $this->delete('post/'.$this->post->id);
        $response->assertSeeText('is_removed');
        $response->assertStatus(200);
    }

    public function testEndorsePostUserList()
    {
        $response = $this
            ->get('get-endorse-users/'.$this->space->id.'/'.$this->post->id);
        $response->assertSeeText('data');
        $response->assertStatus(200);
    }

    public function testUpdatePost()
    {
        $response = $this->actingAs($this->logged_in_user)
            ->patch('post/'.$this->post->id, $this->post->toArray());
        $response->assertSeeText('data');
        $response->assertStatus(200);
    }
}