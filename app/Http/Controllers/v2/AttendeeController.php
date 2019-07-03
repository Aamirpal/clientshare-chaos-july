<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Attendee\AttendeeInterface;
use \Validator;
use App\Models\Attendee;

class AttendeeController extends Controller {

    protected $attendee;

    public function __construct(AttendeeInterface $attendee) {
        $this->attendee = $attendee;
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
                'business_review_id' => 'required|numeric',
                'space_user_ids' => 'required']);
        if (!$validator->fails()) {
            $validator->after(function($validator) use($request) {
                $attendees = $this->attendee->getAttendeesSpaceUserIds($request->business_review_id);
                $common_attendee = [];
                if (sizeOfCustom($attendees)) {
                    $common_attendee = array_intersect($request->space_user_ids, array_column($attendees, 'space_user_id'));
                }
                if (!empty($common_attendee)) {
                    $validator->errors()->add('business_review_id', 'This Attendee already exists.');
                }
            });
        }
        if ($validator->fails()) {
            return apiResponseComposer(400, ['validation_messages' => $validator->errors()], []);
        }
        return apiResponseComposer(200, [], ['attendee' => $this->attendee->createAttendee($request->all())]);
    }

    public function index($business_review_id) {
           $validator = Validator::make(['business_review_id' => $business_review_id], [
                'business_review_id' => 'required|numeric']);

        if ($validator->fails()) {
            return apiResponseComposer(400, ['validation_messages' => $validator->errors()], []);
        }
        return apiResponseComposer(200, [], ['attendee' => $this->attendee->listAttendees($business_review_id, \Auth::user()->id)]);
    }
    public function destroy($business_review_id, $space_user_id) {
        if (!$this->attendee->attendeeExists($business_review_id, $space_user_id)) {
            return apiResponseComposer(400, ['error' => 'This Attendee do not exists in our record.'], []);
        }
        if ($this->attendee->deleteAttendee($business_review_id, $space_user_id)) {
            return apiResponseComposer(200, ['success' => 'Attendee has been deleted successfully.'], []);
        }
        return apiResponseComposer(400, ['error' => 'Something went wrong, please try after some time.'], []);
    }
    

}
