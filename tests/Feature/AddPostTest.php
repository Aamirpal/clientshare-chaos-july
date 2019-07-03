<?php

namespace Tests\Feature;

use Tests\ParentTestClass;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class AddPostTest extends ParentTestClass
{
    
    use WithoutMiddleware;

    private $post_data;
    private $logged_in_user;
    public function setUp(): void
    {
        parent::setUp();

        $this->post_data = [
            'post_description'=>'post body',
            'user_id'=>'7f7121d9-edc1-47eb-8f1a-9c572c0286d9',
            'space_id'=>'3433aabc-d302-11e6-a1de-22000a6388db',
            'space_category_id'=>'123',
            'group_id'=>'1',
            'attachments' => [[
                'originalName' => 'SamplePPTFile.ppt',
                's3_name' => 'post_file/1552978163907.ppt',
                'size' => '1028096',
                'url' => 'https://s3.us-east-1.amazonaws.com/uat-clientshare/post_file/1552978163907.ppt',
                'extention' => 'ppt',
                'mimeType' => 'application/vnd.ms-powerpoint'
            ]],
            'post_subject'=>'post title postmark t1'
        ];
        $this->logged_in_user = factory(\App\Models\User::class)->create();
    }

    public function testExample()
    {
        $response = $this->actingAs($this->logged_in_user)
            ->post('/post', $this->post_data);
            
        $response->assertStatus(200);
    }
}