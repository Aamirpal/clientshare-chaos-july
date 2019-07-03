<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Analytic extends Model {
	const GRAPH = [
		'postintraction_global' => 'postintraction_global',
		'post_global' => 'post_global',
		'space_activities' => 'space_activities',
		'community_graph' => 'communitygraph',
		'nps_graph' => 'nps_graph'
	];

    public static function postInteractionLog($space_id, $month, $year, $company, $current_space,$space_create){
    	$company_name = $company?"||' - '||(select company_name from companies where id = '".$company."')":"||' - '||'All'";
	    $company = $company?"and sp.user_company_id = '".$company."'":"and sp.user_company_id is not null and sp.user_company_id != '00000000-0000-0000-0000-000000000000' ";
	    return $post_interaction = \DB::select(
	     "SELECT month, sum(value) as value, max(share_name)".$company_name." as share_name  from (
	     SELECT count(*) as value,
	     to_char(log.created_at, 'yyyy-mm') as month, max(sps.share_name) as share_name
	     from activity_logs log
	     inner join users usr on usr.id = log.user_id
	     inner join space_users sp on sp.user_id = usr.id and sp.space_id = '".$space_id."'
	     inner join spaces sps on sps.id = sp.space_id
	     left join post_media pm on pm.id::text = log.content_id
	     where (content_type in ('App\PostMedia', 'AppPostMedia') or action in ('view embedded url', 'download post attachment') )
	     and log.space_id = '".$space_id."'
	     ".$company."
	     and log.created_at between (date_trunc('MONTH', '".$year.'-'.$month.'-'.'01'."'::date) + INTERVAL '1 MONTH - 1 day')::date - INTERVAL '12 months'
	     and (date_trunc('MONTH', '".$year.'-'.$month.'-'.'01'."'::date) + INTERVAL '1 MONTH')::date
	     group by 2
	     union all
	     SELECT 0::bigint as value,to_char(generate_series(('".$year.'-'.$month.'-'.'01'."'::date- INTERVAL '11 months') ,'".$year.'-'.$month.'-'.'01'."',interval '1 month')::date, 'yyyy-mm') AS month, (select share_name from spaces where id = '".$space_id."') as share_name
	     ) as com
	     where month > ('".$space_create."'::date - INTERVAL '1 months')::text
	     group by month
	     order by 1 desc limit 12"
	    );
    }

    public static function shareActivityLog($space_id, $month, $year, $company, $current_space, $space_create){
      $company_name = $company?"||' - '||(select company_name from companies where id = '".$company."')":"||' - '||'All'";
      $company = $company?"and su.user_company_id = '".$company."'":"and su.user_company_id is not null and su.user_company_id != '00000000-0000-0000-0000-000000000000'";
      return $post_interaction = \DB::select(
      "SELECT month, sum(value) as value, max(share_name)".$company_name." as share_name from (
      SELECT to_char(log.created_at, 'yyyy-mm') as month, count(*) as value, 
      max(sp.share_name) as share_name
      from activity_logs log
      inner join space_users su on su.space_id::text = log.space_id and su.user_id = log.user_id AND su.deleted_at is null
      inner join spaces sp on sp.id = su.space_id
      where su.space_id = '".$space_id."'
      and log.description not in ('Created a User')
      ".$company."
      and log.created_at between (date_trunc('MONTH', '".$year.'-'.$month.'-'.'01'."'::date) + INTERVAL '1 MONTH - 1 day')::date - INTERVAL '11 months'
      and (date_trunc('MONTH', '".$year.'-'.$month.'-'.'01'."'::date) + INTERVAL '1 MONTH')::date
      group by 1
      union all
      SELECT to_char(generate_series(('".$year.'-'.$month.'-'.'01'."'::date- INTERVAL '11 months') ,'".$year.'-'.$month.'-'.'01'."',interval '1 month')::date, 'yyyy-mm') AS month, 0::bigint as value,  (select share_name from spaces where id = '".$space_id."') as share_name
      ) as com
      where month > ('".$space_create."'::date - INTERVAL '1 months')::text
      group by month
      order by 1"
    );
    }

    public static function totalPostLog($current_space, $month, $year, $company, $space_create) {

    	$company_name = $company?"||' - '||(select company_name from companies where id = '".$company."')":"||' - '||'All'";
        $company = $company?"and su.user_company_id = '".$company."'":"and su.user_company_id is not null and su.user_company_id != '00000000-0000-0000-0000-000000000000'";
    	$post_activities = static::PostActivities($current_space, $month, $year, $company, $space_create, $company_name);

	    $totalposts = \DB::select(
	      "SELECT month, sum(value) as value, max(share_name)".$company_name." as share_name from (
	      SELECT to_char(pst.created_at, 'yyyy-mm') as month, count(pst.id) as value, 
	      max(sp.share_name) as share_name from posts as pst
	      inner join space_users as su ON su.user_id = pst.user_id
	      inner join spaces sp on sp.id = su.space_id
	      where pst.deleted_at IS NULL
	      and pst.space_id = '".$current_space."'
	      and su.space_id = '".$current_space."'
	      and pst.created_at between (date_trunc('MONTH', '".$year.'-'.$month.'-'.'01'."'::date) + INTERVAL '1 MONTH - 1 day')::date - INTERVAL '12 months'
	      and (date_trunc('MONTH', '".$year.'-'.$month.'-'.'01'."'::date) + INTERVAL '1 MONTH')::date
	      ".$company."
	      group by month
	      union all
	      SELECT to_char(generate_series(('".$year.'-'.$month.'-'.'01'."'::date- INTERVAL '11 months') ,'".$year.'-'.$month.'-'.'01'."',interval '1 month')::date, 'yyyy-mm') AS month, 0::bigint as value, (select share_name from spaces where id = '".$current_space."') as share_name
	      ) as tbl_data
	      where month > ('".$space_create."'::date - INTERVAL '1 months')::text
	      group by month
	      order by 1 desc limit 12"
	    );

	    foreach ($totalposts as $month) {
	    	$month->value += !isset($post_activities[$month->month])?0:$post_activities[$month->month]['value'];
	    }
      return $totalposts;
    }

    public static function PostActivities($current_space, $month, $year, $company, $space_create, $company_name) {
    	$post_activities = \DB::select(
	      "SELECT month, sum(value) as value, max(share_name)".$company_name." as share_name from (
	      SELECT to_char(pact.created_at, 'yyyy-mm') as month, count(pst.id) as value, 
	      max(sp.share_name) as share_name from posts as pst
	      inner join space_users as su ON su.user_id = pst.user_id
	      inner join post_activities as pact ON pact.post_id = pst.id
	      inner join spaces sp on sp.id = su.space_id
	      where pst.deleted_at IS NULL
	      and pst.space_id = '".$current_space."'
	      and su.space_id = '".$current_space."'
	      and pst.created_at between (date_trunc('MONTH', '".$year.'-'.$month.'-'.'01'."'::date) + INTERVAL '1 MONTH - 1 day')::date - INTERVAL '12 months'
	      and (date_trunc('MONTH', '".$year.'-'.$month.'-'.'01'."'::date) + INTERVAL '1 MONTH')::date
	      ".$company."
	      group by month	      
	      ) as tbl_data
	      where month > ('".$space_create."'::date - INTERVAL '1 months')::text
	      group by month
	      order by 1 desc limit 12"
	    );
    	return objectToArray(arrayValueToKey($post_activities, 'month'));
    }
}
