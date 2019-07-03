<?php

namespace Tests\Feature;

use Tests\ParentTestClass;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class AwsAccessTokenTest extends ParentTestClass
{
    
    use WithoutMiddleware;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testExample()
    {
        $response = $this->get('aws-access/?q=www.google.com');
        $response->assertStatus(200);
        $response->assertSeeText('message');
    }
}