<?php

namespace App;
use Auth;
use Session;
use Illuminate\Database\Eloquent\Model;
use DB;
use Config;

class ActivityLog extends Model {
	protected $table = 'activity_logs';
    
    protected $fillable = [
        'user_id','content_id','content_type','action','description','details','metadata','space_id'
    ];

    public function customLogs() {
		$data['invitations'] = DB::select(
	    	"SELECT usr.first_name as invited_by, log.description||' to' as action, u2.first_name as invited_to from activity_logs log
			inner join users usr on usr.id = log.user_id
			left join users u2 on u2.id::text = log.metadata->>'invited_to'
			where description = 'Send Invitation'
			and space_id = '".Session::get('space_info')['id']."'
			order by log.created_at desc
		");

		$data['posts'] = Post::with('user', 'postmedia')->where('space_id', Session::get('space_info')['id'])->orderBy('created_at', 'desc')->get();

		$data['attachments'] = DB::select(
			"SELECT usr.first_name, log.action, pm.metadata->>'originalName' as file, count(*)||' Time(s)' as viewed, description from activity_logs log
			inner join users usr on usr.id = log.user_id
			left join post_media pm on pm.id::text = log.content_id
			where (description = 'View Attachment' or description = 'Download Attachment' or action = 'click link' or action = 'view embedded url')
			and space_id = '".Session::get('space_info')['id']."' 
			group by usr.first_name, log.action, pm.metadata->>'originalName', description
		");

		$data['comments'] = DB::select(
			"SELECT usr.first_name, SUBSTR(pst.post_subject,1,10)||'...'as post_subject,  count(*)||' Time(s)'as count, pst.id  from comments cmt
			inner join posts pst on pst.id  = cmt.post_id
			inner join spaces sp on sp.id = pst.space_id
			inner join users usr on usr.id = cmt.user_id
			where pst.space_id = '".Session::get('space_info')['id']."'
			group by usr.first_name, pst.post_subject, pst.id 
			order by max(cmt.created_at) desc
		");

		$data['likes'] = DB::select(
			"SELECT usr.first_name, SUBSTR(pst.post_subject,1,10)||'...'as post_subject, pst.id from endorse_posts lke
			inner join posts pst on pst.id  = lke.post_id
			inner join spaces sp on sp.id = pst.space_id
			inner join users usr on usr.id = lke.user_id
			where pst.space_id = '".Session::get('space_info')['id']."'
			order by lke.created_at desc
		");

		$data['user_mgms'] = DB::select(
			"SELECT u1.first_name as removed_by,u2.first_name as removed_user, log.created_at from activity_logs log
			inner join users u1 on u1.id::text = log.metadata->>'removed_by'
			inner join users u2 on u2.id::text = log.metadata->>'removed_user'
			where description  = 'Remove User'
			and log.space_id = '".Session::get('space_info')['id']."'
			order by log.created_at desc
		");
		
		$data['new_users'] = DB::select(
			"SELECT u1.first_name as invited_to,u2.first_name as invited_by, u1.updated_at, max(log.created_at) from activity_logs log
			inner join users u1 on u1.id::text = log.metadata->>'invited_to'
			inner join users u2 on u2.id = log.user_id
			where description = 'Send Invitation'
			and u1.registration_status = 1
			and log.space_id = '".Session::get('space_info')['id']."'
			group by u1.first_name,u2.first_name, u1.updated_at
			order by u1.updated_at
		");

		return $data;
	}

    public function save(array $options = array()) {
        $this->space_id = $this->space_id??(Session::get('space_info')['id']??'');
        $this->user_id = Auth::user()->id??null;
        parent::save($options);
    }
    public static function userActivityLog($space_id,$start_date,$end_date) {
        return DB::select(
			"SELECT 
				to_char(log.created_at, 'dd-mm-yyyy') as \"Date\", initcap(usr.first_name)||' '||initcap(usr.last_name) as \"Name\",
				comp.company_name as \"Company\",
				pst.post_subject as \"Post Subject\",
				initcap(action) as \"Action\", 
				case when pm.metadata->>'originalName' is not null
					then pm.metadata->>'originalName'
				else
					log.description
				end as \"Content\"
			from activity_logs log
			left join space_users su on su.user_id = log.user_id and su.space_id = '".$space_id."'
			left join companies comp on CASE WHEN su.sub_company_id::text ilike '00000000%' THEN su.user_company_id else su.sub_company_id END = comp.id
			inner join users usr on usr.id = log.user_id
			left join post_media pm on pm.id::text = log.content_id
			left join posts pst on pst.id = pm.post_id or log.content_id = pst.id::text
			where (content_type = 'App\PostMedia' or content_type = 'AppPostMedia' or action = 'view embedded url')
			and log.created_at between  '".$start_date."' and  '".$end_date."'
			and log.space_id = '".$space_id."'
			order by log.created_at;"
		);
    }
    public static function invitationActivityLog($space_id,$start_date,$end_date) {
        return DB::select(
			"SELECT *, count(*) from (
				SELECT to_char(log.created_at, 'YYYY-MM'),case when sender.user_company_id = ( select company_buyer_id from spaces where id = '".$space_id."' )
						then 'Buyer'
					when sender.user_company_id = ( select company_seller_id from spaces where id = '".$space_id."')
						then 'Seller'
					end as tag
				from activity_logs log
				inner join space_users su on su.user_id::text = log.metadata->>'invited_to' and log.space_id = su.space_id::text
				inner join space_users sender on sender.user_id::text = log.metadata->>'invited_by' and log.space_id = su.space_id::text
				--inner join space_users to_usr on to_usr.user_id::text = log.metadata->>'invited_to' and log.space_id = su.space_id::text
				where description = 'Send Invitation'
				and sender.user_company_id != '00000000-0000-0000-0000-000000000000'
				--and su.metadata->>'invitation_code' = '0'
				and sender.space_id = '".$space_id."'
				and log.created_at between  '".$start_date."' and  '".$end_date."'
			) as tbl
			group by tag, to_char
			order by 1;"
		);
    }

    public static function inviteList($pending_invite_value) {
    	return DB::select("select metadata->>'invited_to', concat(usr.first_name::text|| ' ' || usr.last_name::text) as invited_by, space_id, activity_logs.created_at from activity_logs  
                    inner join users usr on usr.id::text = metadata->>'invited_by'
                    where description  ilike 'send Invitation'
                    and metadata->>'invited_to' = '".$pending_invite_value['user_id']."'
                    and space_id = '".$pending_invite_value['space_id']."' order by activity_logs.created_at desc limit 10" );
    }
}
