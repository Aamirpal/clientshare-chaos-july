<?php

namespace Tests\Feature;

use Tests\ParentTestClass;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class GetUrlDataTest extends ParentTestClass
{
    
    use WithoutMiddleware;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testExample()
    {
        $response = $this->get('get-url-data/?q=www.google.com');
        $response->assertStatus(200);
        $response->assertSeeText('domain');
    }
}