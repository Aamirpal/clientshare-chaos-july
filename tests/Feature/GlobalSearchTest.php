<?php

namespace Tests\Feature;

use Tests\ParentTestClass;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;

class GlobalSearchTest extends ParentTestClass
{

    use WithoutMiddleware;
    use WithFaker;


	public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }
    
    public function testRouteReponse()
    {
        $key_word = $this->faker->word();
        $uuid = $this->faker->UUID();
        $counter = rand(1,2);
        $response = $this->get('global-search/'.$key_word.'/'.$uuid.'/'.$this->user->id.'/'.$counter);
        $response->assertStatus(200);
    }
}