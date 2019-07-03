<?php

namespace Tests\Unit;

use Tests\ParentTestClass;
use App\Repositories\Post\PostRepository;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Post;
use App\Models\User;
use App\Models\Space;

class GlobalSearchTest extends ParentTestClass
{
    use WithFaker;
    
	public function setup(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    public function testSearchPosts()
    {
        $key_word = $this->faker->word();
        $uuid = $this->faker->UUID();
        $count = rand(1,2);
        $post = new PostRepository(new post(), new Space());
        $response = $post->searchPosts($uuid,$this->user->id,$key_word,$count);
        $response = is_array($response)? true :false;
        $this->assertTrue($response);
    }
}
