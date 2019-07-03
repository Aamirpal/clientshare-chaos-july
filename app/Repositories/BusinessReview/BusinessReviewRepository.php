<?php
namespace App\Repositories\BusinessReview;

use \App\Repositories\BusinessReview\BusinessReviewInterface;
use App\Models\{
    BusinessReview,
    SpaceUser,
    GroupUser,
    Attendee,
    BusinessReviewMedia,
    Group
};

class BusinessReviewRepository Implements BusinessReviewInterface {

    protected $business_review;
    const BR_LIST_LIMIT = 8;

    public function __construct(BusinessReview $business_review, SpaceUser $space_user, GroupUser $group_user, Attendee $attendee, BusinessReviewMedia $business_review_media, Group $group) {
        $this->business_review = $business_review;
        $this->space_user = $space_user;
        $this->group_user = $group_user;
        $this->attendee = $attendee;
        $this->business_review_media = $business_review_media;
        $this->group = $group;
    }

    public function createBusinessReview($data) {
        return $this->business_review->create($data);
    }
    public function listBusinessReviews($space_id, $offset = null, $limit = null) {
        $limit = ($limit) ?? $this::BR_LIST_LIMIT;
        return $this->business_review->where(['space_id' => $space_id])
                ->with(['businessReviewMedia' => function($query) {
                        return $query->select('business_review_id', 's3_file_path');
                    }])
                ->with(['user' => function($query) {
                        return $query->select('id', 'first_name', 'last_name')->get();
                    }])
                ->withCount('attendee')
                ->offset($offset)
                ->limit($limit)
                ->OrderBy('id', 'Desc')
                ->get()->toArray();
    }
    public function getBusinessReview($id, $current_user_id) {
        $business_review = $this->business_review->where('id', $id)
            ->with(['images', 'videos', 'documents'])
            ->with(['user' => function($query) {
                    return $query->select('id', 'first_name', 'last_name', 'profile_image')
                        ->get();
                }])
            ->withCount('attendee')
            ->first();
        $current_user_exists_in_br_group = $this->group_user->where(['group_id' => $business_review['group_id'], 'user_id' => $current_user_id])->exists();
        $is_everyone_group = $this->group->where(['id' => $business_review['group_id'], 'is_default' => true])->exists();
        $business_review['maximise_view'] = ($current_user_exists_in_br_group || $is_everyone_group);
        if ($current_user_exists_in_br_group || $is_everyone_group) {
            $get_bio = $this->space_user->select('id', 'user_company_id', 'sub_company_id')
                    ->selectRaw("space_users.metadata->'user_profile'->>'job_title' as job_title")
                    ->where(['user_id' => $business_review->user_id, 'space_id' => $business_review->space_id])
                    ->with('userCompany')->first()->toArray();
            $business_review['user_company_profile'] = (isset($get_bio['job_title']) && isset($get_bio['user_company']['company_name'])) ?
                ucfirst($get_bio['job_title']) . " at " . ucfirst($get_bio['user_company']['company_name']) : "";

          $attendees = $this->attendee->where('business_review_id', $id)
            ->with(['spaceUser' => function($query) {
                            return $query->select('id', 'user_id');
                        }])->get()->toArray();
            $business_review['attendees'] = $attendees;
            $attendee_user_ids = [];
            foreach ($attendees as $attendee) {
                $attendee_user_ids[] = $attendee['space_user']['user_id'];
            }
            $business_review['can_delete_attendees'] = in_array($current_user_id, $attendee_user_ids);
        } else {
            unset($business_review['description'], $business_review['review_date']);
        }
        return $business_review;
    }
    public function businessReviewExists($id) {
        return $this->business_review->whereId($id)->exists();
    }
    public function deleteBusinessReview($id) {
        try {
            \DB::transaction(function () use($id) {
                $delete_attendees = $this->attendee->where('business_review_id', $id)->delete();
                $delete_files = $this->business_review_media->where('business_review_id', $id)->delete();
                $this->business_review->whereId($id)->delete();
            }, 5);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function updateBusinessReview($id, $data) {
       return $this->business_review->whereId($id)->update($data);
    }

}
