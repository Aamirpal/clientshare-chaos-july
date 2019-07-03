<?php
namespace App\Repositories\BusinessReviewMedia;

use App\Repositories\BusinessReviewMedia\BusinessReviewMediaInterface;
use App\Models\BusinessReviewMedia;

class BusinessReviewMediaRepository implements BusinessReviewMediaInterface {

    protected $business_review_media;

    public function __construct(BusinessReviewMedia $business_review_media) {
        $this->business_review_media = $business_review_media;
    }

    public function getAttachments($business_review_id) {
        return $this->business_review_media->where('business_review_id', $business_review_id)->get()->toArray();
    }

    public function getAttachmentsById($attachments_id) {
        return $this->business_review_media->whereIn('id', $attachments_id)->get()->toArray();
    }

    public function getAttachmentByUrl($url) {
        return $this->business_review_media->whereRaw("metadata->>'url' = '" . $url . "'")->get()->toArray();
    }

    public function deleteAttachments($attachments_id) {
        return $this->business_review_media->whereIn('id', $attachments_id)->delete();
    }

}
