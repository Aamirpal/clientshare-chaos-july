<?php

namespace App\Helpers;
use Config;
use DB; 
use Carbon\Carbon;
use App\Helpers\Logger;
use Illuminate\Http\Request;
use App\Invitation;
use App\User;
use App\Space;
use App\SpaceUser;
use App\Http\Controllers\ManageShareController;
use Excel;

class bulkInvitation {

	public function bulkInvitationUrl($invite_users){
		$increment = config('constants.COUNT_TWO');
        $invite_data = array();
        foreach ($invite_users['users'] as $user) {
          $user['share_id'] = $invite_users['share_id'];
          $user['user_id'] =  $invite_users['user']->id;
          if(!empty($user['email'])){
            $user_exist = User::getUserIdFromEmail($user['email']);
            $invitation = Invitation::saveInvitedUser($user);
            $already_exist = '';
            if (sizeOfCustom($user_exist)) {
                $space_user = SpaceUser::getSpaceUserInfo($invite_users['share_id'],$user_exist->id);
                if (sizeOfCustom($space_user) && !isset($invite_users['resend_mail'])) {
                    if (!isset($space_user[0]['metadata']['invitation_code']) || ($space_user[0]['metadata']['invitation_code'] == 1)) {
                        $already_exist = trans('messages.validation.already_member_of_share');
                    }
                }
            } 
            if(!empty($already_exist)){
                $invite_url = $already_exist;
            }else{
                $url = env('APP_URL').'/registeruser?invite_id='.$invitation->id;
                $invite_url = (new ManageShareController)->shortUrl($url);
            }              
            $invite_data[] = array(
             "first_name" => $user['first_name'],
             "last_name" => $user['last_name'],
             "email" => $user['email'],
             "invite_url" => $invite_url,
            );
            $increment++;
          }
        }        
       $file = Excel::create('Bulk_CSV_URLs', function($excel) use($invite_data){
          $excel->sheet('First Sheet', function($sheet) use($invite_data){
            $sheet->setOrientation('landscape');
            $sheet->fromArray($invite_data);
          });
        }); 
        return $file;
	}
  public function getShareWithRank($rank){
    return Space::getShare($rank);
  }
}