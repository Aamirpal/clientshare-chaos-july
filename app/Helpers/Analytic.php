<?php

namespace App\Helpers;
use Config;
use DB; 
use Excel;
use App\BouncedEmail;
use App\Space;
use Carbon\Carbon;
use App\Helpers\Logger;
use Illuminate\Http\Request;
use App\Helpers\Analytic;
use App\ActivityLog;
use App\SpaceUser;
use App\Post;
use App\Feedback;
use App\EndorsePost;
use App\Comment;
use App\PostMedia;
use App\PostViews;

class Analytic {


	public function queryArrayFormatter($array){
		
		foreach ($array as $array_key => $array_value) {
			$data[$array[$array_key]->to_char][$array[$array_key]->tag]['count'] = $array[$array_key]->count;
		}
		return $data??[];
	}


	public function bouncedEmails($bounced_emails_data, $invitation_tags, $tags_flag){
		
		$bounced_mails = BouncedEmail::bounceEmails($bounced_emails_data['space_id'], $bounced_emails_data, $invitation_tags, $tags_flag);

		$bounced_mails = (json_decode(json_encode($bounced_mails), true));
		foreach ($bounced_mails as $bounced_mail => $email_data) {
			unset($bounced_mails[$bounced_mail]);
			
			$bounced_mails[$bounced_mail]['Email Address'] = $email_data['to_email'];
			$bounced_mails[$bounced_mail]['Subject'] = $email_data['metadata']['Subject'];
			$bounced_mails[$bounced_mail]['Date'] = Carbon::parse($email_data['metadata']['BouncedAt'])->format('d/m/Y');
			$bounced_mails[$bounced_mail]['Bounce Type'] = $email_data['metadata']['Name'];
			$bounced_mails[$bounced_mail]['Description'] = $email_data['metadata']['Description'];
		}
		return sizeOfCustom($bounced_mails)?$bounced_mails:[['Email Address'=>null, 'Subject'=>null, 'Date'=>null, 'Bounce Type'=>null, 'Description'=>null ]];
	}

	
	public function attachmentLogs($space){
		$activity_log = ActivityLog::userActivityLog($space['space_id'],$space['start_date'],$space['end_date']);

		$replace_label = ['Click Link'=> 'Opened Link', 'View' => 'Viewed', 'Download'=>'Downloaded', 'View Embedded Url'=>'Opened Link'];
		foreach ($activity_log as $activity_log_index => $activity_log_value) {
            $temp_ = json_decode(json_encode($activity_log[$activity_log_index]), true);
            $temp_['Action'] = $replace_label[$temp_['Action']]??$temp_['Action'];
            $temp_['Content'] = str_replace("Click on ", "", $temp_['Content']);
            $temp_['Content'] = str_replace("viewed youtube video ", "", $temp_['Content']);
            $temp_['Date'] = Carbon::parse($temp_['Date'])->format('d/m/Y');
            $pending_invities[] = $temp_;
        }
        return $pending_invities??[];
	}


	public function pendingInvities($analytic_data){

		$space_user_log = SpaceUser::spaceUserLog($analytic_data['space_id'],$analytic_data['start_date'],$analytic_data['end_date']);

		foreach ($space_user_log as $space_user_log_index => $space_user_log_value) {
            $temp_ = json_decode(json_encode($space_user_log[$space_user_log_index]), true);
            $temp_['Date Invited'] = Carbon::parse($temp_['Date Invited'])->format('d/m/Y');
            $temp_['Sent By'] = $temp_['Sent By']??'';
            $temp_['Senders Job Title'] = json_decode($temp_['Senders Job Title'],true)['user_profile']['job_title']??'';
            $pending_invities[] = $temp_;
        }

        return $pending_invities??[];

	}

	
	public function postIntraction($space){
		$space_data = Space::find($space['space_id']);
		$post_interaction_log = Post::postInteractionLog($space['space_id'],$space['start_date'],$space['end_date']);

		foreach ($post_interaction_log as $post_interaction_log_index => $post_interaction_log_value) {
            $temp_ = json_decode(json_encode($post_interaction_log[$post_interaction_log_index]), true);
            if( !$temp_['Category'] || !isset($space_data['category_tags'][$temp_['Category']])) continue;
            $temp_['Category'] = $space_data['category_tags'][$temp_['Category']];
            unset($temp_['pm_id']);unset($temp_['pst_id']);
            $post_intraction[] = $temp_;
        }

        return $post_intraction??[];
	}


	public function shareMembers($space){
		$share_member_log = SpaceUser::shareMembersLog($space['space_id'],$space['start_date'],$space['end_date']);
		foreach ($share_member_log as $share_member_log_key => $share_member_log_value) {
            $temp_ = json_decode(json_encode($share_member_log[$share_member_log_key]), true);
            $temp_['Date Joined'] = Carbon::parse($temp_['Date Joined'])->format('d/m/Y');
            $temp_['Invited By'] = explode('|', $temp_['Invited By'])[1]??'';
            $temp_['Job Title'] = json_decode($temp_['Job Title'],true)['user_profile']['job_title']??'';
            $share_members_final[] = $temp_;
        }

        return $share_members_final??[];
	}


	public function feedback($space) {
		$buyer_feedback = Feedback::buyerFeedback($space['space_id'],$space['start_date'],$space['end_date']);
		return $this->queryArrayFormatter($buyer_feedback);
	}


	public function newMember($space) {
		$new_members =  SpaceUser::newMembers($space['space_id'],$space['start_date'],$space['end_date']);
		return $this->queryArrayFormatter($new_members);
	}

	public function invitation($space) {
		$invitation_activity_log = ActivityLog::invitationActivityLog($space['space_id'],$space['start_date'],$space['end_date']);

		return $this->queryArrayFormatter($invitation_activity_log);
	}

	public function posts($space) {
		$seller_posts = Post::sellerPosts($space['space_id'],$space['start_date'],$space['end_date']);
		return $this->queryArrayFormatter($seller_posts);
	}

	
	public function members($space) {
		$seller_buyer_member = SpaceUser::sellerAndBuyerMembers($space['space_id'],$space['start_date'],$space['end_date']);
		return $this->queryArrayFormatter($seller_buyer_member);
	}


	public function membersDeleted($space) {
		$members_deleted = SpaceUser::membersDeleted($space['space_id'],$space['start_date'],$space['end_date']);
		return $this->queryArrayFormatter($members_deleted);
	}

	public function likes($space) {
		$like_post = EndorsePost::likePost($space['space_id'],$space['start_date'],$space['end_date']);
		return $this->queryArrayFormatter($like_post);
	}


	public function comments($space) {
		$seller_buyer_comment = Comment::sellerBuyerComments($space['space_id'], $space['start_date'], $space['end_date']);
		return $this->queryArrayFormatter($seller_buyer_comment);
	}


	public function postViews($space) {
		$post_view_data = postViews::postViewData($space['space_id'],$space['start_date'],$space['end_date']);
		return $this->queryArrayFormatter($post_view_data);
	}


		
	public function attachments($space) {
		$attachments = PostMedia::Attachments($space['space_id'],$space['start_date'],$space['end_date']);
		return $this->queryArrayFormatter($attachments);
	}


	public function triggerReport( $space_id, int $month, int $year ){
		
		$space = Space::findorfail($space_id);
		if($month==(int)date('m') && $year==(int)date('Y')) {
			$analytic_data['start_date'] = Carbon::parse((string)$space['created_at'])->format('Y-m-d');
			$analytic_data['end_date'] 	 = date("Y-m-d", time() + 86400);
		} else{
			$analytic_data['start_date'] = date("Y-m-d", strtotime($month.'/'.'01/'.$year));
			$ed = date("Y-m-t", strtotime($month.'/'.'01/'.$year));
			$analytic_data['end_date'] = date("Y-m-d", strtotime($ed)+86400);
			$single_month = $analytic_data['start_date'];
		}

		$analytic_data['space_id'] = $space_id;
		
	    $feedback    = (new Analytic)->feedback( $analytic_data );
	    $new_member  = (new Analytic)->newMember( $analytic_data );
	    $invitation  = (new Analytic)->invitation( $analytic_data );
	    $posts       = (new Analytic)->posts( $analytic_data );
	    $members     = (new Analytic)->members( $analytic_data );
	    $members_deleted = (new Analytic)->membersDeleted( $analytic_data );
	    $likes       = (new Analytic)->likes( $analytic_data );
	    $comments    = (new Analytic)->comments( $analytic_data );
	    $post_views  = (new Analytic)->postViews( $analytic_data );
	    $attachments = (new Analytic)->attachments( $analytic_data );

	    $bounced_emails  = (new Analytic)->bouncedEmails( $analytic_data, Config::get('constants.email.invitation_mail_tags'), false );
	    $invitation_bounced_emails  = (new Analytic)->bouncedEmails( $analytic_data, Config::get('constants.email.invitation_mail_tags'), true );

	    $attachment_logs  = (new Analytic)->attachmentLogs( $analytic_data );
	    $pending_invities = (new Analytic)->pendingInvities( $analytic_data );
	    $share_members    = (new Analytic)->shareMembers( $analytic_data );
	    $post_intraction  = (new Analytic)->postIntraction( $analytic_data );

	    $created_date = date_create($space['created_at']);
	    $current_date = date_create(date('Y-m'));
	    $diff  = date_diff($created_date,$current_date);
	    $month_difference = ($diff->m + ($diff->y*12))+1;

	    $output_data[] = [
	        'Month',
	        'Net Promoter Score', 'Feedback Responses',

	        'New Buyers Joined', 'New Sellers Joined', 'Buyers Removed', 'Sellers Removed', 'Total Buyers', 'Total Sellers', 'Total Members',

	        'Invites sent by Buyers', 'Invites sent by Sellers', 'Total invites sent', 
	        'Buyer Posts', 'Seller Posts', 'Total Posts',
	        'Buyer Members Total', 'Seller members Total', 'Total Members', 
	        'Total likes by Buyers', 'Total likes by Sellers', 'Total likes', 
	        'Total comments by Buyers', 'Total comments by Sellers', 'Total comments', 
	        'Views by Buyers', 'Views by Sellers', 'Total views', 
	        'Attachments added by Buyers', 'Attachments added by Sellers', 'Total Attachments',
	    ];



	    $total_seller = $total_buyer = 0;

		for( $i=0; $i<=$month_difference; $i++) {
			if(isset($single_month) && $single_month!=(string)date('Y-m-01', strtotime('-'.$month_difference+$i.' month') )){
				continue;
			}
	        $month = date('Y-m', strtotime('-'.$month_difference+$i.' month') );
	        $output_data[] = [
	            
	            date('M-Y', strtotime('-'.$month_difference+$i.' month') ),
	            (new \App\Http\Controllers\AnalyticsController)->currentMonthNps(
	                date('m', strtotime('-'.$month_difference+$i.' month') ), date('Y', strtotime('-'.$month_difference+$i.' month') ),$space_id
	            )['nps_score']??'NA',
	            $feedback[$month]['Buyer']['count']??0,

				$members[$month]['Buyer']['count']??0,
				$members[$month]['Seller']['count']??0,

				$members_deleted[$month]['Buyer']['count']??0,
				$members_deleted[$month]['Seller']['count']??0,

				(($members[$month]['Buyer']['count']??0)-($members_deleted[$month]['Buyer']['count']??0)+$total_buyer),
				(($members[$month]['Seller']['count']??0)-($members_deleted[$month]['Seller']['count']??0)+$total_seller),

				(($members[$month]['Buyer']['count']??0)-($members_deleted[$month]['Buyer']['count']??0)+$total_buyer)+
				(($members[$month]['Seller']['count']??0)-($members_deleted[$month]['Seller']['count']??0)+$total_seller),
	            
	            
	            $invitation[$month]['Buyer']['count']??0,
	            $invitation[$month]['Seller']['count']??0, 
	            ($invitation[$month]['Seller']['count']??0)+($invitation[$month]['Buyer']['count']??0),

	            
	            $posts[$month]['Buyer']['count']??0,
	            $posts[$month]['Seller']['count']??0, 
	            ($posts[$month]['Seller']['count']??0)+($posts[$month]['Buyer']['count']??0),

	            
	            $members[$month]['Buyer']['count']??0,
	            $members[$month]['Seller']['count']??0, 
	            ($members[$month]['Seller']['count']??0)+($members[$month]['Buyer']['count']??0),

	            
	            $likes[$month]['Buyer']['count']??0,
	            $likes[$month]['Seller']['count']??0, 
	            ($likes[$month]['Seller']['count']??0)+($likes[$month]['Buyer']['count']??0),

	            
	            $comments[$month]['Buyer']['count']??0,
	            $comments[$month]['Seller']['count']??0, 
	            ($comments[$month]['Seller']['count']??0)+($comments[$month]['Buyer']['count']??0),

	            
	            $post_views[$month]['Buyer']['count']??0,
	            $post_views[$month]['Seller']['count']??0, 
	            ($post_views[$month]['Seller']['count']??0)+($post_views[$month]['Buyer']['count']??0),

	            
	            $attachments[$month]['Buyer']['count']??0,
	            $attachments[$month]['Seller']['count']??0, 
	            ($attachments[$month]['Seller']['count']??0)+($attachments[$month]['Buyer']['count']??0),
	            
	         
	        ];
	        $total_buyer += ($members[$month]['Buyer']['count']??0)-($members_deleted[$month]['Buyer']['count']??0);
			$total_seller += ($members[$month]['Seller']['count']??0)-($members_deleted[$month]['Seller']['count']??0);
	    }
	   

        /* Log event */
        (new Logger)->log([
            'description' => 'Analytic xlsx download',
            'action' => 'Analytic xlsx download'
        ]);

        $file =  Excel::create($space->share_name." ".date('M-Y').' Data', function($excel) use( $output_data, $attachment_logs, $pending_invities, $share_members,$post_intraction, $bounced_emails, $invitation_bounced_emails){
            $excel->sheet('General', function($sheet) use($output_data){
                $sheet->fromArray($output_data, null, 'A1', true, false);
            });

           	$excel->sheet('Members', function($sheet) use($share_members){
                $sheet->fromArray($share_members);
            });

           	$excel->sheet('Post Interaction', function($sheet) use($post_intraction){
               $sheet->fromArray($post_intraction, null, 'A1', true, true);
            });

           	$excel->sheet('Pending Invites', function($sheet) use($pending_invities){
                $sheet->fromArray($pending_invities, null, 'A1', true, true);
            });
            $excel->sheet('Action Log', function($sheet) use($attachment_logs){
                $sheet->fromArray($attachment_logs);
            });

            $excel->sheet('Invalid Invites', function($sheet) use($invitation_bounced_emails){
                $sheet->fromArray($invitation_bounced_emails);
            });

            $excel->sheet('Unverified Members', function($sheet) use($bounced_emails){
                $sheet->fromArray($bounced_emails);
            });
        });


       return $file;
    }

}