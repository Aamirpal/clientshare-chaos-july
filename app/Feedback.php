<?php

namespace App;

use DB;
use App\Traits\ModelEventLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class Feedback extends Model {

  use ModelEventLogger;

	protected $casts = ['feedback_month' => 'json'];
	protected $fillable = ['rating'];

  const FEEBACK_RATING_CAP = ['good'=>9, 'bad'=>6];
  const ADMIN_REMINDER_DAYS = [5,10];


  public static function monthNps($space_id, $year, $month){
    return DB::table('feedback as f')
    ->select('f.suggestion','f.comments','f.created_at','f.rating','u.first_name','u.last_name','u.profile_image','f.user_id','u.email')
    ->join('users as u','u.id','f.user_id')
    ->where('f.space_id', '=', $space_id)
    ->whereRaw("(extract(year from (feedback_month->0->>'date')::date) = $year or extract(year from (feedback_month->1->>'date')::date) = $year or extract(year from (feedback_month->2->>'date')::date) = $year)")
    ->whereRaw("(extract(month from (feedback_month->0->>'date')::date) = $month or extract(month from (feedback_month->1->>'date')::date) = $month or extract(month from (feedback_month->2->>'date')::date) = $month)")
    ->orderBy('u.first_name')
    ->get()->toArray();
  }

  public static function npsSpaceFeedbacks($space_id, $year, $month, $space_created_at){
    return DB::select("SELECT * from (SELECT generate_series(('".$year.'-'.$month.'-'.'01'."'::date- INTERVAL '11 months') ,'".$year.'-'.$month.'-'.'01'."',interval '1 month')::date AS month, 
      0::bigint as value, (select share_name from spaces where id = '$space_id') as share_name) as tbl
      where month > ('".$space_created_at."'::date - INTERVAL '1 months')");
  }

  public static function feedbackQuaters($space_id, $show_current_month_quater){
    return DB::select("SELECT * from (SELECT date_trunc('month', created_at) as created_at, max(feedback_month) as feedback_month from ((SELECT max(created_at) as created_at , max(feedback_month::text) as feedback_month from feedback
      where space_id = '".$space_id."'
      and (date_trunc('month', current_date) != date_trunc('month', created_at) ".$show_current_month_quater.")
      group by (date_trunc('month', current_date) != date_trunc('month', created_at))
      )
      union all
      (SELECT created_at, metadata::text as feedback_month  from activity_logs
      where action = 'feedback close'
      and space_id = '".$space_id."'
      )) as tbl
      group by date_trunc('month', created_at) ) as tbl
      order by 1;");
  }

  public static function submittedFeedbackUsers($space_id, $month, $year){
    return DB::table('feedback as f')
      ->select('f.suggestion','f.comments','f.created_at','f.rating','u.first_name','u.last_name','u.profile_image','f.user_id','u.email')
      ->join('users as u','u.id','f.user_id')
      ->where('f.space_id', '=', $space_id)
      ->whereYear('f.created_at', '=', $year)
      ->whereMonth('f.created_at', '=', $month)
      ->orderBy('u.first_name')
      ->get()->toArray();
  }

  public static function spaceFeedbacks($space_id, $selection_method, $order_by=null) {
    $query = static::space($space_id);
    if($order_by)$query->orderBy('created_at', $order_by);
    return $query->$selection_method();

  }

  public static function submittedFeedbacks($space_id, $selection_method){
    return static::submittedFeedback($space_id)->$selection_method();
  }

  public function getIdAttribute($value) {
    return (string) $value;
  }

  /**/
  public function scopeSpace($query, $space_id){
    return $query->where('space_id', $space_id);
  }

  /**/
  public function scopeSubmittedFeedback($query, $space_id){
    return $query->where('space_id', $space_id)
    ->whereRaw("to_char(created_at, 'mm-yyyy') = to_char(now(), 'mm-yyyy')");
  }

  public function userCurrentQuaterFeedback($space_id){
    return $this->where('space_id', $space_id)
      ->where('user_id', Auth::user()->id)
      ->whereRaw("to_char(created_at, 'mm-yyyy') = to_char(now(), 'mm-yyyy')")
      ->get();
  }

  /**/
  public static function pendingFeedbackSpaces(){
    return DB::select(
      "SELECT sp.*, comp.company_name
      from space_users su
      inner join spaces sp on sp.id = su.space_id and su.user_company_id = sp.company_buyer_id
      inner join companies comp on comp.id = su.user_company_id
      inner join users usr on su.user_id = usr.id 
      left join feedback fb on fb.user_id = usr.id and su.space_id = fb.space_id 
        and to_char(fb.created_at, 'mm-yyyy') = to_char(now(), 'mm-yyyy')
      where fb.id is null
      and sp.feedback_status is true
      and to_char(sp.feedback_status_to_date, 'mm-yyyy') = to_char(now(), 'mm-yyyy')
      group by sp.id, comp.company_name"
    );
  }

  /**/
  public static function feedbackCloseShareUsersList(){
    return DB::select(
      "SELECT email, sp.*, usr.*, sp.id as space_id, usr.id as u_id from space_users su
      inner join spaces sp on su.space_id = sp.id
      inner join users usr on usr.id = su.user_id
      where sp.feedback_status is true
      and su.metadata->>'user_profile' !=''
      and su.deleted_at is null
      and to_char(sp.feedback_status_to_date, 'mm') = to_char(now(), 'mm')
      and to_char(sp.feedback_status_to_date, 'yy') = to_char(now(), 'yy')"
    );
  }
  public static function buyerFeedback($space_id,$start_date,$end_date){
    return DB::select(
      "SELECT to_char(fb.created_at, 'YYYY-MM'), 'Buyer'::text as tag, count(*) 
      from feedback fb
      inner join space_users su on fb.space_id = su.space_id and fb.user_id = su.user_id
      where su.space_id = '".$space_id."'
      and fb.created_at between  '".$start_date."' and  '".$end_date."'
      group by to_char(fb.created_at, 'YYYY-MM');");
  }

  public function userFeedback($space_id,$year,$month){
    return DB::table('feedback as f')
                  ->select('f.suggestion','f.comments','f.created_at','f.rating','u.first_name','u.last_name','u.profile_image','f.user_id','u.email')
                  ->join('users as u','u.id','f.user_id')
                  ->where('f.space_id', '=', $space_id)
                  ->whereYear('f.created_at', '=', $year)
                  ->whereMonth('f.created_at', '=', $month)
                  ->orderBy('u.first_name')
                  ->get()->toArray();
  }
   
   
}
