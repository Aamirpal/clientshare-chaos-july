<?php

namespace App\Repositories\Attendee;

interface AttendeeInterface {

    public function createAttendee($data);

    public function listAttendees($business_review_id, $current_user_id);

    public function getAttendeesSpaceUserIds($business_review_id);

    public function attendeeExists($business_review_id, $space_user_id);

    public function deleteAttendee($business_review_id, $space_user_id);

    public function createDefaultAttendee($business_review_id, $space_user_id);

    public function updateAttendee($business_review_id, $data);
}
