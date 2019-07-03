<?php

namespace Tests\Feature;

use Tests\ParentTestClass;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\Space;
use App\Models\User;

class NotificationCountTest extends ParentTestClass {

    /**
     * A basic test example.
     *
     * @return void
     */
    use WithoutMiddleware;

    public function setUp(): void {
        parent::setUp();
        $this->space = factory(Space::class)->create();
        $this->User = factory(User::class)->create();
    }

    public function testShareNotificationsAPi() {
        $response = $this->get('share-notifications/'.$this->space->id.'/'.$this->User->id);
        $response->assertStatus(200);
    }

}
