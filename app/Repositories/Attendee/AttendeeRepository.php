<?php

namespace App\Repositories\Attendee;

use \App\Repositories\Attendee\AttendeeInterface;
use App\Models\{
    Attendee,
    BusinessReview
};

class AttendeeRepository Implements AttendeeInterface {

    protected $attendee, $business_review;

    public function __construct(Attendee $attendee, BusinessReview $business_review) {
        $this->attendee = $attendee;
        $this->business_review = $business_review;
    }

    public function createAttendee($data) {
        foreach ($data['space_user_ids'] as $space_user_id) {
            $attendee = new $this->attendee(['business_review_id' => $data['business_review_id'], 'space_user_id' => $space_user_id]);
            $attendees[] = ( $attendee->save()) ? $attendee : [];
        }
        return $attendees;
    }
    public function createDefaultAttendee($business_review_id, $space_user_id) {
        return $this->attendee->create(['business_review_id' => $business_review_id, 'space_user_id' => $space_user_id, 'is_default' => true]);
    }
    public function listAttendees($business_review_id, $current_user_id = null) {
        $attendee = $this->attendee->where(['business_review_id' => $business_review_id])
                ->with(['spaceUser' => function($query) {
                        return $query->select('id', 'user_id');
                    }])->get()->toArray();
    }

    public function getAttendeesSpaceUserIds($business_review_id) {
        return $this->attendee->select('space_user_id')->where(['business_review_id' => $business_review_id])->get()->toArray();
    }
    public function attendeeExists($business_review_id, $space_user_id) {
        return $this->attendee->where(['business_review_id' => $business_review_id, 'space_user_id' => $space_user_id])->exists();
    }
    public function deleteAttendee($business_review_id, $space_user_id) {
        return $this->attendee->where(['business_review_id' => $business_review_id, 'space_user_id' => $space_user_id])->delete();
    }
   public function updateAttendee($business_review_id, $data) {
        $existing_attendees = $this->attendee->select('space_user_id')->where('business_review_id', $business_review_id)->where('is_default', false)->get()->toArray();
        $existing_space_user_ids = [];
        if (sizeOfCustom($existing_attendees)) {
            $existing_space_user_ids = array_column($existing_attendees, 'space_user_id');
        }
        $deleted_attendees = array_diff($existing_space_user_ids, $data['space_user_ids']);
        if (sizeOfCustom($deleted_attendees)) {
             foreach ($deleted_attendees as $attendee_space_user_id) {
                $this->attendee->where(['space_user_id' => $attendee_space_user_id, 'business_review_id' => $business_review_id, 'is_default' => false])->delete();
            }
        }
        $attendees = [];
        foreach ($data['space_user_ids'] as $space_user_id) {
            if (!$this->attendee->where(['space_user_id' => $space_user_id, 'business_review_id' => $business_review_id])->exists()) {
                $attendee = new $this->attendee(['business_review_id' => $business_review_id, 'space_user_id' => $space_user_id]);
                $attendees[$space_user_id] = ( $attendee->save()) ? $attendee : [];
            }
        }
        return $attendees;
    }

}
