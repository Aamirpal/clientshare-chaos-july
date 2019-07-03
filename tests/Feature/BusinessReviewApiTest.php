<?php

namespace Tests\Feature;

use Tests\ParentTestClass;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\{
    User,
    BusinessReview
};

class BusinessReviewApiTest extends ParentTestClass {

    use WithoutMiddleware;

    public function setUp(): void {
        parent::setUp();
        $this->business_review = factory(BusinessReview::class)->create();
        $this->user = factory(User::class)->create();
    }

     public function testBusinessReviews() {
        $response = $this->get('list-business-reviews/' . $this->business_review->user_id . '/' . $this->business_review->space_id);
        $response->assertSeeText('business_review');
        $response->assertSeeText('offset');
        $response->assertStatus(200);
    }

    public function testShowBusinessReview() {
        $response = $this->actingAs($this->user)
            ->get('business-review/' . $this->business_review->id);
        $response->assertSeeText('data');
        $response->assertSeeText('id');
        $response->assertStatus(200);
    }
    public function testDeleteBusinessReview() {
        $response = $this->delete('business-review/' . $this->business_review->id);
        $response->assertSeeText('deleted successfully');
        $response->assertStatus(200);
    }

    public function testListAttendeesForBusinessReviews() {
        $response = $this->actingAs($this->user)
            ->get('list-attendees/' . $this->business_review->id);
        $response->assertSeeText('attendee');
        $response->assertStatus(200);
    }

}
