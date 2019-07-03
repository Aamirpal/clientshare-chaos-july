<?php
namespace App\Http\Controllers;
use Session;
use Redirect;
use Auth;
use DB;
use Storage;
use App\{Analytic,Company};
use Hash;
use App\SpaceUser;
use App\Helpers\Logger;
use App\Post;
use App\User;
use App\Space;
use Illuminate\Http\Request;
use DateTime;
use Response;
use App\Feedback;
use mikehaertl\wkhtmlto\Pdf as PDF;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\ManageShareController;
use Carbon\Carbon;
use App\ActivityLog;
use ZipArchive;
use App\Traits\OneTimePassport;
use Illuminate\Support\Facades\{Input, Config};

class AnalyticsController extends Controller {
  use OneTimePassport;

  /* */
  public function global_analytics_final(Request $request=null, $data=null){
    $data = $data??$request->all();
    if(!isset($data['Shares'])) return [];
    $shares= $this->getSelectedSharerecord($data['Shares']);
    if(empty($data['month']) || empty($data['year'])) return 0;
    $data['month'] = (!empty($data['month']))?$data['month']:Carbon::now()->month;
    $data['year'] = (!empty($data['year']))?$data['year']:Carbon::now()->year;
    foreach ($shares as $key => $value) {
      $space_created_at = $value['created_at'];
      if(empty(trim($value['created_at']))){
         $space_created = Space::spaceByIdGetCreatedAt($value['share_id'], 'created_at');
         $space_created_at = $space_created['created_at'];
      }
      if($value['all']){
        $current_post_analytics = $this->post_interaction( $value['share_id'], $data['month'], $data['year'], '', $current_space=null, $space_created_at);
        if(sizeOfCustom($current_post_analytics)) $final['post_interaction'][] = $current_post_analytics;
        $current_post_analytics = $this->totalPosts($value['share_id'], $data['month'], $data['year'], '', $space_created_at);
        if(sizeOfCustom($current_post_analytics)) $final['total_posts'][] = $current_post_analytics;
        $current_post_analytics = $this->share_activity( $value['share_id'], $data['month'], $data['year'], '', $value['share_id'], $space_created_at);
        if(sizeOfCustom($current_post_analytics)) $final['csi'][] = $current_post_analytics;
        $current_post_analytics = $this->currentMonthMembers($value['share_id'], $data['month'], $data['year'], '', $space_created_at);
        if(sizeOfCustom($current_post_analytics)) $final['currentMonthMembers'][] = $current_post_analytics;
        $current_post_analytics = $this->feedbackNps($value['share_id'],$data['month'], $data['year'],$space_created_at,$value['share_name']);
        if(sizeOfCustom($current_post_analytics)) $final['nps'][] = $current_post_analytics;
      }
      foreach ($value['company'] as $company_id => $value2 ) {
        $current_post_analytics = $this->post_interaction( $value['share_id'], $data['month'], $data['year'], $company_id, $current_space=null, $space_created_at);
        if(sizeOfCustom($current_post_analytics)) $final['post_interaction'][] = $current_post_analytics;
        $current_post_analytics = $this->totalPosts($value['share_id'], $data['month'], $data['year'], $company_id, $space_created_at);
        if(sizeOfCustom($current_post_analytics)) $final['total_posts'][] = $current_post_analytics;
        $current_post_analytics = $this->share_activity( $value['share_id'], $data['month'], $data['year'], $company_id, $value['share_id'], $space_created_at);
        if(sizeOfCustom($current_post_analytics)) $final['csi'][] = $current_post_analytics;
        $current_post_analytics = $this->currentMonthMembers($value['share_id'], $data['month'], $data['year'], $company_id, $space_created_at);
        if(sizeOfCustom($current_post_analytics)) $final['currentMonthMembers'][] = $current_post_analytics;
      }
    }
    
    if(!isset($final)) return 0;
    foreach ($final as $key => $value) {
      foreach ($value as $key_l2 => $value_l2) {
        foreach ($value_l2 as $key_l3 => $value_l3) {
          $data_temp = (array)$value_l3;
          $data_temp['share_name'] = str_replace("'", "", $data_temp['share_name']);
          $final[$key][$data_temp['month']]['month'] = $data_temp['month'];
          $final[$key][$data_temp['month']][$data_temp['share_name']] =$data_temp['value'];
          unset($final[$key][$key_l2]);
        }
      }
      $i=0; unset($sort_val);
      foreach ($final[$key] as $key_l4 => $value_l4) {
        $final[$key][$i] = $value_l4;
        $sort_val[$i] = $key_l4;
        unset($final[$key][$key_l4]);
        $i++;
      }
      array_multisort($sort_val, SORT_ASC, $final[$key]);
    }
    $final['month'] = $data['month'];
    $final['year'] = $data['year'];
    return $final;
  }


  /* */
  public function globalAnalyticsFinal(Request $request=null, $data=null, $graph){
    $data = $data??$request->all();
    if(!isset($data['Shares'])) return [];
    $shares= $this->getSelectedSharerecord($data['Shares']);

  if(empty($data['month']) || empty($data['year'])) return 0;
    $data['month'] = (!empty($data['month']))?$data['month']:Carbon::now()->month;
    $data['year'] = (!empty($data['year']))?$data['year']:Carbon::now()->year;
    foreach ($shares as $key => $value) {
      $space_created_at = $value['created_at'];
      if(empty(trim($value['created_at']))){
         $space_created = Space::spaceByIdGetCreatedAt($value['share_id'], 'created_at');
         $space_created_at = $space_created['created_at'];
      }
      if($value['all']){
        switch($graph){
          case Analytic::GRAPH['postintraction_global']:
            $current_post_analytics = $this->post_interaction( $value['share_id'], $data['month'], $data['year'], '', $current_space=null, $space_created_at);
            if(sizeOfCustom($current_post_analytics)) $final['post_interaction'][] = $current_post_analytics;
            break;
          case Analytic::GRAPH['post_global']:
            $current_post_analytics = $this->totalPosts($value['share_id'], $data['month'], $data['year'], '', $space_created_at);
            if(sizeOfCustom($current_post_analytics)) $final['total_posts'][] = $current_post_analytics;
            break;
          case Analytic::GRAPH['space_activities']:
            $current_post_analytics = $this->share_activity( $value['share_id'], $data['month'], $data['year'], '', $value['share_id'], $space_created_at);
            if(sizeOfCustom($current_post_analytics)) $final['csi'][] = $current_post_analytics;
            break;
          case Analytic::GRAPH['community_graph']:
            $current_post_analytics = $this->currentMonthMembers($value['share_id'], $data['month'], $data['year'], '', $space_created_at);
            if(sizeOfCustom($current_post_analytics)) $final['currentMonthMembers'][] = $current_post_analytics;
          break;
          case Analytic::GRAPH['nps_graph']:
            $current_post_analytics = $this->feedbackNps($value['share_id'],$data['month'], $data['year'],$space_created_at,$value['share_name']); 
            if(sizeOfCustom($current_post_analytics)) $final['nps'][] = $current_post_analytics;
          break;
        }
      }
      foreach ($value['company'] as $company_id => $value2 ) {
        switch($graph) {
          case Analytic::GRAPH['postintraction_global']:
            $current_post_analytics = $this->post_interaction( $value['share_id'], $data['month'], $data['year'], $company_id, $current_space=null, $space_created_at);
            if(sizeOfCustom($current_post_analytics)) $final['post_interaction'][] = $current_post_analytics;
            break;
          case Analytic::GRAPH['post_global']:
            $current_post_analytics = $this->totalPosts($value['share_id'], $data['month'], $data['year'], $company_id, $space_created_at);
            if(sizeOfCustom($current_post_analytics)) $final['total_posts'][] = $current_post_analytics;
            break;
          case Analytic::GRAPH['space_activities']:
            $current_post_analytics = $this->share_activity( $value['share_id'], $data['month'], $data['year'], $company_id, $value['share_id'], $space_created_at);
            if(sizeOfCustom($current_post_analytics)) $final['csi'][] = $current_post_analytics;
            break;
          case Analytic::GRAPH['community_graph']:
            $current_post_analytics = $this->currentMonthMembers($value['share_id'], $data['month'], $data['year'], $company_id, $space_created_at);
            if(sizeOfCustom($current_post_analytics)) $final['currentMonthMembers'][] = $current_post_analytics;
          break;
        }
      }
      
    }

    if(!isset($final)) return 0;
    foreach ($final as $key => $value) {
      foreach ($value as $key_l2 => $value_l2) {
        foreach ($value_l2 as $key_l3 => $value_l3) {
          $data_temp = (array)$value_l3;
          $data_temp['share_name'] = str_replace("'", "", $data_temp['share_name']);
          $final[$key][$data_temp['month']]['month'] = $data_temp['month'];
          $final[$key][$data_temp['month']][$data_temp['share_name']] =$data_temp['value'];
          unset($final[$key][$key_l2]);
        }
      }
      $i=0; unset($sort_val);
      foreach ($final[$key] as $key_l4 => $value_l4) {
        $final[$key][$i] = $value_l4;
        $sort_val[$i] = $key_l4;
        unset($final[$key][$key_l4]);
        $i++;
      }
      array_multisort($sort_val, SORT_ASC, $final[$key]);
    }
    $final['month'] = $data['month'];
    $final['year'] = $data['year'];
    return $final;
  }

  public function getSelectedSharerecord($shares){
      if(!empty($shares)){
        $i=0;
        foreach ($shares as $key => $value) {
            $data['shares'][$i]['share_id'] = $key;
            $data['shares'][$i]['created_at'] = $value['created_at']??'';
            $data['shares'][$i]['share_name'] = $value['share_name']??'';
            $data['shares'][$i]['company'] = $value['company']??[];
            $data['shares'][$i]['all'] = $value['all']??0;
            $data['shares'][$i]['company_id'] = isset($value['company']) && sizeOfCustom($value['company']) == 1 ? array_keys($value['company'])[0]:'';
            $i++;
        }
        if(!empty($data['shares']))
          return $data['shares'];

        return[];
      }
      return [];
  }

  public function global_analytics_v3(Request $request, $graph='csi_global'){
    if(empty($request)){
      $data = $request->all();
      if(!isset($data['Shares'])){
        $empty_html = '<div class="greyout"><p>Please select any share to view graph.</p></div>';
        $final_data[$graph] = $empty_html;
        return $final_data;
      } 
    }
    $final = $this->globalAnalyticsFinal($request, null, $graph);
    $empty = [0=>['month'=>'', 'n/a'=>'0']];

    switch($graph){
      case Analytic::GRAPH['space_activities']:
        $final_data['space_activities'] = View::make('analytics/graphs/common_graph', ['view_data'=>['data_id'=>'csi_view_data','csi_view_data' => $final['csi']??$empty, 'graph_legends_class'=>'csi_graph_legends', 'graph_div_id'=>'csi_graph_div_id' ]] )->render();
        break;

      case Analytic::GRAPH['postintraction_global']:
        $final_data['postintraction_global'] = View::make('analytics/graphs/common_graph', ['view_data'=>['data_id'=>'post_interaction_view_data','post_interaction_view_data' => $final['post_interaction']??$empty, 'graph_legends_class'=>'post_interaction_graph_legends', 'graph_div_id'=>'post_interaction_graph_div_id' ]] )->render();
      break;

      case Analytic::GRAPH['post_global']:
        $final_data['post_global'] = View::make('analytics/graphs/common_graph', ['view_data'=>['data_id'=>'post_view_data','post_view_data' => $final['total_posts']??$empty, 'graph_legends_class'=>'post_global_graph_legends', 'graph_div_id'=>'post_global_graph_div_id' ]] )->render();
      break;

      case Analytic::GRAPH['community_graph']:
        $final_data['communitygraph'] = View::make('analytics/graphs/common_graph', ['view_data'=>['data_id'=>'community_view_data','community_view_data' => $final['currentMonthMembers']??$empty, 'graph_legends_class'=>'community_graph_legends', 'graph_div_id'=>'community_graph_div_id' ]] )->render();
      break;

      case Analytic::GRAPH['nps_graph']:
        $final_data['nps_graph'] = View::make('analytics/graphs/common_graph', ['view_data'=>['data_id'=>'nps_view_data','nps_view_data' => $final['nps']??$empty, 'graph_legends_class'=>'nps_graph_legends', 'graph_div_id'=>'nps_graph_div_id' ]] )->render();
      break;
    }
    return $final_data;
  }

  /* */
  public function global_analytics_v2(Request $request=null){
    if(empty($request)){
      $data = $request->all();
      if(!isset($data['Shares'])){
        $empty_html = '<div class="greyout"><p>Please select any share to view graph.</p></div>';
        $final_data['postintraction_global'] = $empty_html;
        $final_data['post_global'] = $empty_html;
        $final_data['communitygraph'] = $empty_html;
        $final_data['currentMonthMembers'] = $empty_html;
        $final_data['csi_global'] = $empty_html;
        return $final_data;
      } 
    }
    $final = $this->global_analytics_final($request);
    $empty = [0=>['month'=>'', 'n/a'=>'0']];    
    $final_data['postintraction_global'] = View::make('analytics/graphs/common_graph', ['view_data'=>['data_id'=>'post_interaction_view_data','post_interaction_view_data' => $final['post_interaction']??$empty, 'graph_legends_class'=>'post_interaction_graph_legends', 'graph_div_id'=>'post_interaction_graph_div_id' ]] )->render();

    $final_data['post_global'] = View::make('analytics/graphs/common_graph', ['view_data'=>['data_id'=>'post_view_data','post_view_data' => $final['total_posts']??$empty, 'graph_legends_class'=>'post_global_graph_legends', 'graph_div_id'=>'post_global_graph_div_id' ]] )->render();

    $final_data['communitygraph'] = View::make('analytics/graphs/common_graph', ['view_data'=>['data_id'=>'community_view_data','community_view_data' => $final['currentMonthMembers']??$empty, 'graph_legends_class'=>'community_graph_legends', 'graph_div_id'=>'community_graph_div_id' ]] )->render();

    $final_data['currentMonthMembers'] = View::make('analytics/graphs/common_graph', ['view_data'=>['data_id'=>'nps_view_data','nps_view_data' => $final['nps']??$empty, 'graph_legends_class'=>'nps_graph_legends', 'graph_div_id'=>'nps_graph_div_id' ]] )->render();

    $final_data['csi_global'] = View::make('analytics/graphs/common_graph', ['view_data'=>['data_id'=>'csi_view_data','csi_view_data' => $final['csi']??$empty, 'graph_legends_class'=>'csi_graph_legends', 'graph_div_id'=>'csi_graph_div_id' ]] )->render();

    return $final_data;
  }

  /* */
  public function share_activity( $space_id, $month, $year, $company=NULL, $current_space=null,$space_create) {
    return Analytic::shareActivityLog($space_id, $month, $year, $company, $current_space, $space_create);
  }

  /* */
  public function post_interaction( $space_id, $month, $year, $company=NULL, $current_space=null,$space_create) {
    return Analytic::postInteractionLog($space_id, $month, $year, $company, $current_space,$space_create);
  }

  /* Global analytics */
  public function index(Request $request, $share_id=null) {
    (new ManageShareController)->setClientShareList(); 
    (new Logger)->log([
      'action' => 'visit analytics',
      'description' => 'visit analytics'
      ]);
    $usr_id = Auth::user()->id;
    if(Auth::user()->user_type_id == Config::get('constants.ADMIN_ROLE_ID')){
      $user_shares = Space::getSpaceUserWithMetaData();
    }else{
      $user_shares = Space::getSpaceUserByUserId(Auth::user()->id);
    }
    if(!sizeOfCustom($user_shares)) abort(404);
    $last_accessed_space = $share_id?['last_space'=>$share_id]:json_decode(Auth::user()->active_space, true);
    User::where('id', Auth::user()->id)->update(['active_space' => json_encode($last_accessed_space) ]);
    $company_id = $last_accessed_space['last_space']??$user_shares[0]['id'];
    $temp_req['Shares'][$company_id]['company'] = [];
    $temp_req['Shares'][$company_id]['all'] = 1;
    /* update session*/
    if(Auth::user()->user_type_id != Config::get('constants.ADMIN_ROLE_ID')){
      $sess_data = Space::where('id',$company_id)->with('BuyerName','SellerName')->get()[0];
      $space_user = SpaceUser::getSpaceUserRole($company_id,Auth::user()->id);
      $sess_data['space_user'] = $space_user;
      Session::put('space_info', $sess_data); 
    }
    /* update session*/
    $temp_req['month'] = Carbon::now()->month;
    $temp_req['year'] = Carbon::now()->year;
    $data = $this->global_analytics_final($request, $temp_req);
    $data['user_shares'] = $user_shares;
    $data['select_share_id'] = $company_id; 
    foreach ($data['user_shares'] as $key => $value) {
      $data['date_filter_temp'][Carbon::parse($value['created_at'])->year][] = Carbon::parse($value['created_at'])->month;
    } 
    $min_year = min(array_keys($data['date_filter_temp']));
    $min_month = min($data['date_filter_temp'][$min_year]);
    for($i=0; (!(Carbon::parse($min_month.'/01/'.$min_year)->addMonth($i-1)->month == Carbon::now()->month && Carbon::parse($min_month.'/01/'.$min_year)->addMonth($i-1)->year == Carbon::now()->year)); $i++){
      $data['date_filter'][Carbon::parse($min_month.'/01/'.$min_year)->addMonth($i)->year][] = Carbon::parse($min_month.'/01/'.$min_year)->addMonth($i)->month;
    } 
    foreach ($data['date_filter'] as $key => $value) {      
      $data['date_filter'][$key] = array_reverse($data['date_filter'][$key]);
    }
    $data['graph_color_code'] = ['#046380','#E74C3C','#3498DB','#468966','#FF9800','#8A0917','#FF358B','#57385C','#714C36','#FFED75','#A3CD39'];
    (new Logger)->mixPannelInitial(Auth::user()->id, $company_id, Logger::MIXPANEL_TAG['analytics_opened'], null);
    return view('analytics/index_global', ['data'=>$data]);
  }

/**/
  public function downloadGraphs(Request $request, $otp_id){
    $req = $request->all();    
    $data = $this->otpGetUrl($otp_id);
    $dateToTest = $data['metadata']['year'].'-'.$data['metadata']['month'].'-01';
    $final = $this->global_analytics_final($request, $data['metadata']);
    $final['header'] = date('F',strtotime($dateToTest)). ' analytics report';    
    $view = view::make('pdf_analytics', ['data'=>$final]);
    $contents = $view->render();
    if( isset($req['apple_dev']) && $req['apple_dev']){
      return $contents;
    }
    $path = \h4cc\WKHTMLToPDF\WKHTMLToPDF::PATH;
    $pdf = new PDF(array('binary'=>$path));

    $pdf->addPage($contents);
    $pdf->send($final['header'].'.pdf');
    return;
  }

  public function graphs(Request $request){
    $url = env('APP_URL').'/download_graphs';
    $otp = $this->generate_otp(['app_url'=>$url, 'method'=>'get', 'metadata'=>$request->all()] );
    (new Logger)->mixPannelInitial(Auth::user()->id, null, Logger::MIXPANEL_TAG['download_analytics_PDF']);
    return $url = $url.'/'.$otp['id'];
  }


  /* total posts */
  public function totalPosts($current_space, $month, $year, $company=NULL, $space_create){
      return Analytic::totalPostLog($current_space, $month, $year, $company, $space_create);
  }

  /* */
  public function currentMonthMembers($share_id,$month, $year, $company=NULL, $space_create){
    $company_name = $company?"||' - '||(select company_name from companies where id = '".$company."')":"||' - '||'All'";
    $company = $company?"and su.user_company_id = '".$company."'":"and su.user_company_id is not null and su.user_company_id != '00000000-0000-0000-0000-000000000000' ";
    return DB::select(
      "SELECT month, (sum(sum(value) -max(deleted)) OVER (ORDER BY month ASC)) AS value, share_name $company_name as share_name from (
      SELECT month, max(count) AS value, share_name, 0::bigint as deleted from (
      SELECT sp.share_name, to_char(doj, 'yyyy-mm') as month, count(*)
      from space_users su
      inner join spaces sp on sp.id = su.space_id
      where 
      su.metadata ->> 'invitation_code' = '1'
      $company
      and su.metadata ->> 'user_profile' != '' 
      and space_id ='$share_id'
      group by 1,2 order by month desc
      ) as tbl
      group by 1,3 
      --order by month desc
      union all
      SELECT to_char(generate_series(('".$year.'-'.$month.'-'.'01'."'::date- INTERVAL '11 months') ,'".$year.'-'.$month.'-'.'01'."',interval '1 month')::date, 'yyyy-mm') AS month, 0::bigint as value, (select share_name from spaces where id = '$share_id') as share_name, 0::bigint as deleted
      union all
      SELECT to_char(doj, 'yyyy-mm') as month, 0::bigint as value, sp.share_name, count(su.id) as deleted
      from space_users su
      inner join spaces sp on sp.id = su.space_id
      where (su.deleted_at IS NOT NULL and su.deleted_at < (date_trunc('MONTH', '".$year.'-'.$month.'-'.'01'."'::date) + INTERVAL '1 MONTH - 1 day')::date)
      and su.metadata ->> 'invitation_code' = '1'
      $company
      and su.metadata ->> 'user_profile' != '' and space_id ='$share_id' 
      group by 1,2,3
      ) as tb2
      where month > ('".$space_create."'::date - INTERVAL '1 months')::text
      and month < to_char((date_trunc('MONTH', '".$year.'-'.$month.'-'.'01'."'::date) + INTERVAL '1 MONTH'), 'yyyy-mm')
      group by 1,3
      order by month desc limit 12
      ");
  }


  private function getSixMonthsValue($month, $year, $data){
      $months_set = ['Dec','Nov','Oct','Sep','Aug','Jul','Jun','May','Apr','Mar','Feb','Jan'];
      $share_name = $data[0]->share_name??0;
      if(!$share_name) return [];
      $start_index = 12- $month;
      $end_index = $start_index+11;
      $previous_year = false;
      for($i=$start_index;$i<=$end_index;$i++){
        $obj = new \stdClass();
        if($i>=12){
          $new_i = $i-12;
          $newyear = $year-1;
          $obj->day=$months_set[$new_i];
          $obj->year = $newyear;
          $obj->month = $newyear.'-'.date("m", strtotime($months_set[$new_i]));
          $obj->value = 0;
          $obj->share_name = $share_name;
          $default_values["{$months_set[$new_i]}_{$newyear}"] = $obj;
        }else{
          $obj->day=$months_set[$i];
          $obj->year = $year;
          $obj->month = $year.'-'.date("m", strtotime($months_set[$i]));
          $obj->value = 0;
          $obj->share_name = $share_name;
          $default_values["{$months_set[$i]}_{$year}"] = $obj;
        }
      }
      $count_total = 0;
      foreach($data as $set){
        $default_values["{$set->day}_{$set->year}"] = $set;
      }
      return $default_values;
  }
  public function prevMonthMembers($spaceid,$month, $year, $company=NULL, $current_space){
    $dateToTest = $year.'-'.$month.'-01';
    $date = $year.'-'.$month.'-'.date('t',strtotime($dateToTest));//get last day of diven month and year
    $date = date('Y-m-d', strtotime(date($date)." -1 month"));
    $month = date('m',strtotime($date));
    $year = date('Y',strtotime($date));
    if(!empty($company)){//if no company filter
      $check_company = "and metadata #>> '{user_profile,company}'='$company'";
    }else{
      $check_company='';
    }
    $currentMonthMembers =  DB::select("select count(*) from space_users where space_id = '$current_space' and user_status='0' and  metadata ->> 'invitation_code' = '1' and  metadata ->> 'user_profile' != '' and  EXTRACT(MONTH FROM doj) = '$month' and EXTRACT(YEAR FROM doj) ='$year' $check_company ");
    if($currentMonthMembers[0]->count){
      return $currentMonthMembers[0]->count;
    }else{
      return 0;
    }
  }
  public function currentMonthPosts($spaceid,$month, $year, $company=NULL, $current_space){
    $currentMonth = date('m');
    $currentMonthPosts = DB::select("select count(*) from posts where space_id = '$current_space' and where deleted_at IS NULL and EXTRACT(MONTH FROM created_at) = '$month' AND EXTRACT(YEAR FROM created_at) = '$year'");
    if($currentMonthPosts[0]->count){
      return $currentMonthPosts[0]->count;
    }else{
      return 0;
    }
  }

  public function currentMonthNps($month, $year,$space_id){
    $year = $year??date('m');
    $month = $month??date('m');
    $feedback = Feedback::monthNps($space_id, $year, $month);
    if(empty($feedback)) return null;
    foreach ($feedback as $val) {
      if($val->rating >= Feedback::FEEBACK_RATING_CAP['good']) {
        $feedback_rating['good'][] = $val->rating;
      } elseif($val->rating > Feedback::FEEBACK_RATING_CAP['bad'] && $val->rating < Feedback::FEEBACK_RATING_CAP['good']){
        $feedback_rating['medium'][] = $val->rating;
      }else {
        $feedback_rating['bad'][] = $val->rating;
      }
    }
    $feedback_rating['good'] = sizeOfCustom($feedback_rating['good']??[]);
    $feedback_rating['medium'] = sizeOfCustom($feedback_rating['medium']??[]);
    $feedback_rating['bad'] = sizeOfCustom($feedback_rating['bad']??[]);
    $total  = $feedback_rating['good'] + $feedback_rating['medium'] + $feedback_rating['bad'];
    $average_good_response = ($feedback_rating['good']/$total)*100;
    $average_bad_response = ($feedback_rating['bad']/$total)*100;
    $nps = $average_good_response - $average_bad_response;
    $nps = round($nps);
    return ['nps_score'=>$nps, 'nps_mg'=>''];
  }

  public function feedbackNps($space_id,$month,$year,$space_created_at,$share_name=NULL){
    $feedbacks = Feedback::npsSpaceFeedbacks($space_id, $year, $month, $space_created_at);
    $last_feedback_submitted = Feedback::spaceFeedbacks($space_id, 'first', 'desc');
    foreach ($feedbacks as $feedback_key => $feedback) {
      $current_month_nps  = $this->currentMonthNps(Carbon::parse($feedback->month)->month, Carbon::parse($feedback->month)->year,$space_id);
      $nps[$feedback_key]['share_name'] = $share_name;
      $nps[$feedback_key]['month'] = Carbon::parse($feedback->month)->format('Y-m');
      $nps[$feedback_key]['value'] = $current_month_nps['nps_score']??null;
      if(is_null($current_month_nps) && (Carbon::parse($last_feedback_submitted['created_at'])->format('Y-m') <= Carbon::parse($feedback->month)->format('Y-m')) ) $nps[$feedback_key]['value']=null;
    }
    return $nps??[];
  }

  public function exportXlsFile(Request $request) {
    (new Logger)->mixPannelInitial(Auth::user()->id, null, Logger::MIXPANEL_TAG['download_analytics']);
    (new Logger)->mixPannelInitial(Auth::user()->id, null, Logger::MIXPANEL_TAG['export_analytics_data']);
    
    $job_data['logged_in_user'] = Auth::user();

    if(!empty($request->all()['share_id'])) {
      $job_data['report_data'] = $request->all();
      $job_data['user'] = User::findorfail(Auth::user()->id);
      $job_data['share'] = Space::findorfail( $job_data['report_data']['share_id'] );
      $job_data['mail']['subject'] = $job_data['share']['share_name'].' '.trans('messages.mail_subject.analytics_report');
      dispatch(new \App\Jobs\ShareAnalyticReport($job_data));
      return;

    } elseif(isset($request->spaces) && sizeOfCustom($request->spaces)) {
      $job_data['spaces'] = (new Space)->getSpacesListById($request->spaces);
    } else {
      $job_data['spaces'] = Space::getActiveShares($job_data['logged_in_user']);
    }

    $job_data['data'] = $request->all();
    dispatch(new \App\Jobs\MakeAndSendAnalyticsReport($job_data));
    return;
  }

}