<?php

namespace App\Repositories\BusinessReviewMedia;

interface BusinessReviewMediaInterface {

    public function getAttachments($business_review_id);

    public function getAttachmentsById($attachments_id);

    public function getAttachmentByUrl($url);

    public function deleteAttachments($attachments_id);
}
