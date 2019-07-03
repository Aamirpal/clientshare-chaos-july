<?php
namespace Tests\Feature;

use Tests\ParentTestClass;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\{Comment, User};

class CommentControllerTest extends ParentTestClass
{
    use WithoutMiddleware;

    public function setUp(): void
    {
        parent::setUp();
        $this->comment = factory(Comment::class)->create();
        $this->logged_in_user = User::find($this->comment->user_id);
    }

    public function testAddComment()
    {
        $response = $this->actingAs($this->logged_in_user)->post('add-comment', $this->comment->toArray());
        $response->assertSeeText('data');
        $response->assertStatus(200);
    }

    // public function testUpdateComment()
    // {
    //     $response = $this->actingAs($this->logged_in_user)->patch('update-comment/'.$this->comment->id, $this->comment->toArray());
    //     $response->assertSeeText('data');
    //     $response->assertStatus(200);
    // }

    public function testDeleteComment()
    {
        $response = $this->delete('delete-comment/'.$this->comment->id);
        $response->assertSeeText('data');
        $response->assertStatus(200);
    }
}