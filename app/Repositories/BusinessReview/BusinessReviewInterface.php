<?php
namespace App\Repositories\BusinessReview;

interface BusinessReviewInterface {

     public function createBusinessReview($data);
     public function listBusinessReviews($space_id, $offset, $limit);
     public function getBusinessReview($id, $user_id);
     public function businessReviewExists($id);
     public function deleteBusinessReview($id);
     public function updateBusinessReview($id, $data);
}
