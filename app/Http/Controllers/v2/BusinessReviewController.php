<?php

namespace App\Http\Controllers\v2;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\BusinessReview\BusinessReviewInterface;
use App\Repositories\Attendee\AttendeeInterface;
use App\Repositories\SpaceUser\SpaceUserInterface;
use App\Repositories\BusinessReviewMedia\BusinessReviewMediaInterface;
use App\Repositories\RemoveCloudFile\RemoveCloudFileInterface;
use Validator;

class BusinessReviewController extends Controller {

    protected $business_review, $business_review_media, $attendee, $space_user, $remove_cloud_files;
    const LIST_LIMIT = 8;

    use \App\Traits\v2\BusinessReviewTrait;

    public function __construct(BusinessReviewInterface $business_review, AttendeeInterface $attendee, SpaceUserInterface $space_user, BusinessReviewMediaInterface $business_review_media, RemoveCloudFileInterface $remove_cloud_files) {
        $this->business_review = $business_review;
        $this->business_review_media = $business_review_media;
        $this->attendee = $attendee;
        $this->space_user = $space_user;
        $this->remove_cloud_files = $remove_cloud_files;
    }

    public function store(Request $request) {
        $data = $request->all();
     
        $vaidate_request = $this->validateBusinessReviewRequest($data);
        if ($vaidate_request->fails()) {
            return apiResponseComposer(400, ['validation_messages' => $vaidate_request->errors()], []);
        }
        $post_attachments['attachments'] = $data['attachments'] ?? [];
        $attendee_data['space_user_ids'] = $data['space_user_ids'] ?? [];
        unset($data['attachments'], $data['space_user_ids']);

        $business_review = $this->business_review->createBusinessReview($data);
        if (!empty($post_attachments['attachments'])) {
            $this->saveBrAttachment($post_attachments, $business_review->id);
        }
        if (!empty($attendee_data['space_user_ids'])) {
            $attendee_data['business_review_id'] = $business_review->id;
            $this->attendee->createAttendee($attendee_data);
        }
        if (!empty($business_review)) {
            $default_attendee = $this->space_user->getOneSpaceUserInfo($data['space_id'], $data['user_id']);
            $this->attendee->createDefaultAttendee($business_review->id, $default_attendee->id);
        }
        return apiResponseComposer(200, [], ['business_review' => $business_review]);
    }

    public function index(Request $request) {
        $limit = $this::LIST_LIMIT;
        return apiResponseComposer(200, [], ['business_review' => $this->business_review->listBusinessReviews($request->space_id, $request->offset, $limit), 'offset' => ($request->offset) ? ($request->offset) + $limit : $limit]);
    }
    public function show(Request $request) {
       return apiResponseComposer(200, [], ['business_review' => $this->business_review->getBusinessReview($request->id, \Auth::user()->id)]);
    }

    public function destroy($id) {
        $vaidate_request = $this->validateBusinessReviewId(['business_review_id' => $id]);
        if ($vaidate_request->fails()) {
            return apiResponseComposer(400, ['validation_messages' => $vaidate_request->errors()], []);
        }
        if (!$this->business_review->businessReviewExists($id)) {
            return apiResponseComposer(400, ['error' => 'This business review do not exists in our record.'], []);
        }
        if ($this->business_review->deleteBusinessReview($id)) {
            return apiResponseComposer(200, ['success' => 'Business review has been deleted successfully.'], []);
        }
        return apiResponseComposer(400, ['error' => 'Some technical error occured. Please try after some time.'], []);
    }
    public function update(Request $request, $id) {
        $data = $request->all();
        $vaidate_request = $this->validateBusinessReviewRequest($data, $id);
        if ($vaidate_request->fails()) {
            return apiResponseComposer(400, ['validation_messages' => $vaidate_request->errors()], []);
        }
        $attachments['attachments'] = $data['attachments'] ?? [];
        $attendee_data['space_user_ids'] = $data['space_user_ids'] ?? [];
        unset($data['attachments'], $data['space_user_ids'], $data['delete_attachments']);

        $this->updateBrAttachments($request, $id);
        $business_review = $this->business_review->updateBusinessReview($id, $data);
        if (!empty($attendee_data['space_user_ids'])) {
            $this->attendee->updateAttendee($id, $attendee_data);
        }
        if ($business_review) {
            return apiResponseComposer(200, ['success' => 'Business review has been updated successfully.'], $this->business_review->getBusinessReview($id, \Auth::user()->id));
        }
        return apiResponseComposer(400, ['error' => 'Some technical error occured. Please try after some time.'], []);
       
    }

}
