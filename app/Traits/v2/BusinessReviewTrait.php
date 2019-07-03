<?php

namespace App\Traits\v2;

use Validator;
use App\Models\BusinessReviewMedia;

trait BusinessReviewTrait {

    protected $business_review_id;

    public function validateBusinessReviewRequest($request, $id = null) {
        $rules = [
            'title' => 'required|Max:30',
            'description' => 'required',
            'group_id' => 'required',
            'conducted_via' => 'required',
            'review_date' => 'required|date'
        ];
        if (!$id) {
            $rules['user_id'] = 'required|uuid';
            $rules['space_id'] = 'required|uuid';
        }
        $validator = Validator::make($request, $rules, [
                'title.required' => 'Please enter a title',
                'review_date.required' => 'Please enter the date of the review',
                'description.required' => 'Please add some information about the review',
                'conducted_via.required' => 'Please select how the review was conducted'
                ]
        );
        return $validator;
    }
    public function validateBusinessReviewId($request) {

        $validator = Validator::make($request, [
                'business_review_id' => 'required|numeric',
                ]
        );
        return $validator;
    }

    public function saveBrAttachment($data, $business_review_id) {
        try {
            $media = [];
            foreach ($data['attachments'] as $key => $value) {
                $media[] = BusinessReviewMedia::create([
                        'business_review_id' => $business_review_id,
                        's3_file_path' => filePathUrlToJson($value['url'], false),
                        'metadata' => $value
                ]);
            }
            return $media;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function updateBrAttachments($request, $business_review_id) {
        if ($request->delete_attachments) {
            $request->delete_attachments = array_map(function ($attachment) {
                return $attachment['attachmentID'];
            }, $request->delete_attachments);
            $delete_attachments = $this->business_review_media->getAttachmentsById($request->delete_attachments);
            $this->remove_cloud_files->logFiles($delete_attachments);
            $this->business_review_media->deleteAttachments($request->delete_attachments);
        }

        $attachments['attachments'] = $request->attachments;
        foreach ($attachments['attachments'] as $key => $attachment) {
            if ($this->business_review_media->getAttachmentByUrl($attachment['url'])) {
                unset($attachments['attachments'][$key]);
            }
        }
        $this->saveBrAttachment($attachments, $business_review_id);
        return $request->all();
    }

}
