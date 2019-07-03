<?php

namespace App;
use DB;

use Illuminate\Database\Eloquent\Model;

class ManagementInformation extends Model{

    const RAG_FILTER_LABEL = [
      'l1' => 'Last 7 days',
      'l2' => 'Last 8 to 13 days',
      'l3' => 'Over 14 days'
    ];

    const COMMUNITY_SORT = [
      'community_buyer_total' => 'buyers',
      'community_buyer_filter' => 'cal_buyers',

      'community_supplier_total' => 'sellers',
      'community_supplier_filter' => 'cal_sellers',

      'community_overall_total' => 'over_all',
      'community_overall_filter' => 'over_all_performance'
    ];
    const POST_SORT = ['buyer_posts_total'=>'buyer_posts_total',
      'buyer_posts_change'=>'buyer_posts_change',
      'supplier_posts_total'=>'supplier_posts_total',
      'supplier_posts_change'=>'supplier_posts_change',
      'overall_posts_total'=>'overall_posts_total',
      'overall_posts_change'=>'overall_posts_change'
    ];
    const POST_INTERACTIONS = ['buyer_post_interactions'=>'buyer_interations',
      'seller_post_interactions' => 'seller_interations',
      'post_interactions' => 'total_interations'
    ];
    const CSI_SORT = [
      'buyer_csi_score_this_month' => 'buyer_csi_score_this_month',
      'buyer_csi_score_change' => 'buyer_csi_score_change',
      'seller_csi_score_this_month' => 'seller_csi_score_this_month',
      'seller_csi_score_change' => 'seller_csi_score_change',
      'overall_csi_score' => 'overall_csi_score',
      'overall_csi_score_change' => 'overall_csi_score_change'
    ];

	public static function community($request){		
		
		$date = "(to_date('$request->date_value', 'YYYY-MM-DD')+interval '1 day')";
    $offset=0;
    $order_by='';

    $spaces = $request->spaces ? "and su.space_id in ('".implode("','", $request->spaces)."')" : '';

        $year = date('Y', strtotime($request->date_value));
        $month = date('m', strtotime($request->date_value));
        $date = date('d', strtotime($request->date_value));

        $from_date = " '" . ($year) . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01 00:00:00'";
        $to_date = " '" . ($year) . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-{$date} 23:59:59' ";
        
    if(isset(static::COMMUNITY_SORT[$request->sort])){
      if(!empty($request->suppliers) && !empty($request->buyers)) {
          $spaces = "and (spaces.company_seller_id in ('".implode("','", $request->suppliers)."') OR  spaces.company_buyer_id in ('".implode("','", $request->buyers)."'))";
      }else if(!empty($request->suppliers)) {
          $spaces = "and spaces.company_seller_id in ('".implode("','", $request->suppliers)."')";
      }else if(!empty($request->buyers)) {
          $spaces = "and spaces.company_buyer_id in ('".implode("','", $request->buyers)."')";
      }    
      $order_by = "order by ".static::COMMUNITY_SORT[$request->sort]." ".$request->sort_order;
      $offset = $request->offset;
    }

    if(!empty(array_filter($request->status_filter))){
         $spaces .= "and spaces.status in ('".implode("','", $request->status_filter)."')";
    }

    $RAG_filter = self::getRagFilter(['RAG_filter'=>$request->RAG_filter]);

    if(is_array($RAG_filter) && sizeOfCustom($RAG_filter)){
      $RAG_filter = " and spaces.id in('" . implode("', '", $RAG_filter) . "')";
    } else $RAG_filter = '';
    $limit_offset = !$request->disable_offset ? "limit $request->limit offset $offset":'';

		$data = DB::select("SELECT 
			space_id,
			sum(buyer) as buyers,
			sum(new_buyer) - sum(removed_buyer) as cal_buyers,

			sum(seller) as sellers,
			sum(new_seller) - sum(removed_seller) as cal_sellers,

			sum(buyer)+sum(seller) as over_all,
			(sum(new_seller) - sum(removed_seller)) + (sum(new_buyer) - sum(removed_buyer)) as over_all_performance
		from (
		select
			su.space_id,
			case when spaces.company_seller_id = su.user_company_id and doj <= " . $to_date . "
				then 1
				else 0
			end as seller,

			case when spaces.company_seller_id = su.user_company_id and doj between " . $from_date . " and " . $to_date . "
				then 1
				else 0
			end as new_seller,

			
			case when spaces.company_buyer_id = su.user_company_id and doj <= " . $to_date . "
				then 1
				else 0
			end as buyer,
			
			case when spaces.company_buyer_id = su.user_company_id and doj between " . $from_date . " and  " . $to_date . "
				then 1
				else 0
			end as new_buyer,

			case when spaces.company_buyer_id = su.user_company_id and su.deleted_at between " . $from_date . " and  " . $to_date . "
				then 1
				else 0
			end as removed_buyer,

			case when spaces.company_seller_id = su.user_company_id and su.deleted_at between " . $from_date . " and  " . $to_date . "
				then 1
				else 0
			end as removed_seller
		from space_users su
		right join spaces spaces on spaces.id = su.space_id
		where spaces.deleted_at is null
		and su.metadata->>'invitation_code' = '1'
		and su.deleted_at is null 
    $RAG_filter
		$spaces
		) as tbl
		group by space_id
		$order_by
		$limit_offset
		");

		return arrayValueToKey(json_decode(json_encode($data), true), 'space_id');

	}

	public static function getCsiData($params) {
        $spaces = Space::select('id', 'company_seller_id', 'company_buyer_id');
        $space_ids = [];
        $run_spaces_query = false;
        $order_by = '';
        $limit = 'LIMIT '.$params['limit'];
        $offset = 'OFFSET '.$params['offset'];
        if (!empty($params['spaces'])) {
            $run_spaces_query = true;
            $space_ids = $params['spaces'];
            $limit = '';
            $offset = '';
        }

        if(isset(static::CSI_SORT[$params['sort']])) {
            if (!empty($params['suppliers']) && !empty($params['buyers'])) {
                $run_spaces_query = true;
                $spaces->whereIn('company_seller_id', $params['suppliers'])
                       ->orWhereIn('company_buyer_id', $params['buyers']);
            }
            else if (!empty($params['suppliers'])) {
                $run_spaces_query = true;
                $spaces->whereIn('company_seller_id', $params['suppliers']);
            }
            else if (!empty($params['buyers'])) {
                $run_spaces_query = true;
                $spaces->whereIn('company_buyer_id', $params['buyers']);
            }

            if(!empty(array_filter($params['status_filter']))){
               $spaces->whereIn('status', $params['status_filter']);
             } 
            
          $spaces_data = $spaces->get()->toArray();
          $space_ids = array_column($spaces_data, 'id');
          $limit = 'LIMIT '.$params['limit'];
          $offset = 'OFFSET '.$params['offset'];
          $order_by = " ORDER BY " . static::CSI_SORT[$params['sort']] . " " . $params['sort_order'];
        }

        $year = date('Y', strtotime($params['date_value']));
        $month = date('m', strtotime($params['date_value']));
        $date = date('d', strtotime($params['date_value']));
        
        $last_month = \Carbon\Carbon::create($year, $month, $date)->subMonth(1);

        $this_month_from_date = " '" . ($year) . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01 00:00:00'";
        $this_month_to_date = " '" . ($year) . "-" . str_pad($month--, 2, '0', STR_PAD_LEFT) . "-{$date} 23:59:59' ";

        $days_in_last_month = date('t', mktime(0, 0, 0, $month, 1, $year));
        if($date > $days_in_last_month) {
            $date = $days_in_last_month;
        }

        $last_month_from_date = " '" . ($last_month->year) . "-" . str_pad($last_month->month, 2, '0', STR_PAD_LEFT) . "-01 00:00:00' ";
        $last_month_to_date = " '" . ($last_month->year) . "-" . str_pad($last_month->month, 2, '0', STR_PAD_LEFT) . "-{$date} 23:59:59' ";

        $and_space_id_condition = '';
        if(!empty($space_ids)) {
            $and_space_id_condition = "AND spaces.id in ('" . implode("', '", $space_ids) . "')";
        }

        $limit_offset = !$params['disable_offset'] ? "$limit $offset":'';

        $rag_filter = self::getRagFilter($params);

        if(is_array($rag_filter) && sizeOfCustom($rag_filter)){
          $rag_filter = " and spaces.id in('" . implode("', '", $rag_filter) . "')";
        } else $rag_filter = '';

        $sql = "SELECT space_id,
                        SUM(buyer_csi_score_this_month) AS buyer_csi_score_this_month,
                        CASE WHEN SUM(buyer_csi_score_last_month) = 0 THEN 0 ELSE ((((SUM(buyer_csi_score_this_month) - SUM(buyer_csi_score_last_month)) * 100 ) /  SUM(buyer_csi_score_last_month) )) END  AS buyer_csi_score_change,
                        SUM(seller_csi_score_this_month) AS seller_csi_score_this_month,
                        CASE WHEN SUM(seller_csi_score_last_month) = 0 THEN 0 ELSE ((((SUM(seller_csi_score_this_month) - SUM(seller_csi_score_last_month))  * 100 ) / SUM(seller_csi_score_last_month) ) ) END AS seller_csi_score_change,
                        SUM(buyer_csi_score_this_month) + SUM(seller_csi_score_this_month) AS overall_csi_score,
                        CASE WHEN (SUM(buyer_csi_score_last_month) + SUM(seller_csi_score_last_month)) = 0 THEN 0 ELSE ( ( ( ( (SUM(buyer_csi_score_this_month) + SUM(seller_csi_score_this_month)) - (SUM(buyer_csi_score_last_month) + SUM(seller_csi_score_last_month))) * 100  / 
                            (SUM(buyer_csi_score_last_month) + SUM(seller_csi_score_last_month)) ) )) END AS overall_csi_score_change
                 FROM
                   (select spaces.id as space_id,
                           CASE
                               when spaces.company_buyer_id = su.user_company_id 
                                  AND a.description not in ('Created a User') 
                                  AND su.deleted_at is null 
                                  AND a.created_at between (($this_month_from_date)) AND (($this_month_to_date)) then 1
                               else 0
                           END AS buyer_csi_score_this_month,
                           CASE
                               when spaces.company_buyer_id = su.user_company_id 
                                 AND a.description not in ('Created a User') 
                                 AND su.deleted_at is null 
                                 AND a.created_at between (($last_month_from_date)) AND (($last_month_to_date)) then 1
                               else 0
                           END AS buyer_csi_score_last_month,
                           CASE
                               when spaces.company_seller_id = su.user_company_id 
                                 AND a.description not in ('Created a User') 
                                 AND su.deleted_at is null 
                                 AND a.created_at between (($this_month_from_date)) AND (($this_month_to_date)) then 1
                               else 0
                           END AS seller_csi_score_this_month,
                           CASE
                               when spaces.company_seller_id = su.user_company_id 
                                 AND a.description not in ('Created a User') 
                                 AND su.deleted_at is null 
                                 AND a.created_at between (($last_month_from_date)) AND (($last_month_to_date)) then 1
                               else 0
                           END AS seller_csi_score_last_month
                    FROM activity_logs AS a
                    RIGHT JOIN spaces AS spaces ON a.space_id = spaces.id::text
                    LEFT JOIN space_users as su ON su.user_id = a.user_id and su.space_id::text = a.space_id
                    WHERE spaces.deleted_at is null
                    $rag_filter
                    AND su.deleted_at is null
                      $and_space_id_condition
                  ) AS tbl
                 GROUP BY space_id
                 $order_by
                 $limit_offset
                 ";
        $log_data = DB::select($sql);
        $csi_data = arrayValueToKey(json_decode(json_encode($log_data), true), 'space_id');
        return $csi_data;
    }

    public static function getTotalPostsBySpaces($filters, $selection_method = 'count')
    {
        $filter_where = '';
        $orderby = $limit = $offset = '';
        $join_company = '';
        if(!empty($filters['spaces'])) {
            $filter_where = "and spaces.id in ('".implode("','", $filters['spaces'])."')";
        }else{
          $limit = 'limit 0';
          $offset =  'offset 0';
        }

        if(isset(static::POST_SORT[$filters['sort']])){
            if(!empty($filters['suppliers']) && !empty($filters['buyers'])) {
                $filter_where = "and (spaces.company_seller_id in ('".implode("','", $filters['suppliers'])."') OR  spaces.company_buyer_id in ('".implode("','", $filters['buyers'])."'))";
            }else if(!empty($filters['suppliers'])) {
                $filter_where = "and spaces.company_seller_id in ('".implode("','", $filters['suppliers'])."')";
            }else if(!empty($filters['buyers'])) {
                $filter_where = "and spaces.company_buyer_id in ('".implode("','", $filters['buyers'])."')";
            }

            if(!empty(array_filter($filters['RAG_filter']))) {
              $filter_where .= " and spaces.id in ('".implode("','", self::getRagFilter($filters))."')";
            }

            if(!empty(array_filter($filters['status_filter']))){
              $filter_where .= "and spaces.status IN ('".implode("','",$filters['status_filter'])."')";
            } 

            $orderby = 'order by '.$filters['sort'].' '.$filters['sort_order']; 
            $limit = 'limit '.$filters['limit'];
            $offset =  'offset '.$filters['offset'];
        }
      
        $post_activities = static::postMiQuery($filters, $filter_where, true);
        $posts = static::postMiQuery($filters, $filter_where, false);

        $limit_offset = !$filters['disable_offset'] ? "$limit $offset":'';
        $overall = DB::select(
          "SELECT space_id as id, 
            SUM(seller_total) as supplier_posts_total, 
            SUM(seller_total) - (SUM(old_seller_total) + SUM(removed_seller_total)) as supplier_posts_change,
            SUM(buyer_total) as buyer_posts_total, 
            SUM(buyer_total) - (SUM(old_buyer_total) + SUM(removed_buyer_total)) as buyer_posts_change,
            SUM(seller_total) + SUM(buyer_total) as overall_posts_total, 
            SUM(seller_total) + SUM(buyer_total) - (SUM(old_seller_total) + SUM(old_buyer_total)) - (SUM(removed_seller_total) + SUM(removed_buyer_total)) as overall_posts_change
          from ( 
            ".$post_activities."
            union all
            ".$posts."

          ) as tbl
          group by id $orderby
          $limit_offset"
        );
        return arrayValueToKey(objectToArray($overall), 'id');
    }

    public static function postMiQuery($filters, $filter_where, $post_activities) {

      if($post_activities) {
        $join = "left join post_activities on posts.id = post_activities.post_id";
        $model = "post_activities";
      } else {
        $join = "";
        $model = "posts";
      }

      $query = "SELECT
        spaces.id as space_id,
        case when spaces.company_seller_id = space_users.user_company_id and {$model}.created_at <= '".$filters['date']."' and (posts.deleted_at is null OR posts.deleted_at > '".$filters['date']."')
            then 1
            else 0
        end as seller_total,
        case when spaces.company_seller_id = space_users.user_company_id and posts.deleted_at is null and {$model}.created_at <= '".$filters['old_date']."'
            then 1
            else 0
        end as old_seller_total,
        case when spaces.company_seller_id = space_users.user_company_id and {$model}.created_at <= '".$filters['old_date']."' and posts.deleted_at between '".$filters['old_date']."' and '".$filters['date']."'
            then 1
            else 0
        end as removed_seller_total,
        case when spaces.company_buyer_id = space_users.user_company_id and {$model}.created_at <= '".$filters['date']."' and (posts.deleted_at is null OR posts.deleted_at > '".$filters['date']."')
            then 1
            else 0
        end as buyer_total,
        case when spaces.company_buyer_id = space_users.user_company_id and posts.deleted_at is null and {$model}.created_at <= '".$filters['old_date']."'
            then 1
            else 0
        end as old_buyer_total,
        case when spaces.company_buyer_id = space_users.user_company_id and {$model}.created_at <= '".$filters['old_date']."' and posts.deleted_at between '".$filters['old_date']."' and '".$filters['date']."'
            then 1
            else 0
        end as removed_buyer_total
      from posts
      {$join}
      right join spaces on spaces.id = posts.space_id
      left join space_users on space_users.user_id = posts.user_id and space_users.space_id = posts.space_id
      where spaces.deleted_at is null ".$filter_where;

      return $query;
    }

    public static function getPostInteractionData($filters){
        $filter_where = '';
        $orderby = $limit = $offset = '';
        $join_company = '';
        if(!empty($filters['spaces'])) {
            $filter_where = "and spaces.id in ('".implode("','", $filters['spaces'])."')";
        }else{
          $limit = 'limit 0';
          $offset =  'offset 0';
        }

        if(isset(static::POST_INTERACTIONS[$filters['sort']])) {
            if(!empty($filters['suppliers']) && !empty($filters['buyers'])) {
                $filter_where = "and (spaces.company_seller_id in ('".implode("','", $filters['suppliers'])."') OR  spaces.company_buyer_id in ('".implode("','", $filters['buyers'])."'))";
            }else if(!empty($filters['suppliers'])) {
                $filter_where = "and spaces.company_seller_id in ('".implode("','", $filters['suppliers'])."')";
            }else if(!empty($filters['buyers'])) {
                $filter_where = "and spaces.company_buyer_id in ('".implode("','", $filters['buyers'])."')";
            }

            if(!empty(array_filter($filters['RAG_filter']))) {
              $filter_where .= " and spaces.id in ('".implode("','", self::getRagFilter($filters))."')";
            }

            if(!empty(array_filter($filters['status_filter']))){
               $filter_where .= "and spaces.status IN ('".implode("','",$filters['status_filter'])."')";
            } 

            $orderby = 'order by '.static::POST_INTERACTIONS[$filters['sort']].' '.$filters['sort_order']; 
            $limit = 'limit '.$filters['limit'];
            $offset =  'offset '.$filters['offset'];
        }
        $limit_offset = !$filters['disable_offset'] ? "$limit $offset":'';
        $posts_interactions = DB::select("SELECT space_id as id,
                        SUM(buyer_total) as buyer_interations,
                        SUM(seller_total) as seller_interations,
                        SUM(buyer_total) + SUM(seller_total) as total_interations
                        from ( select
                              spaces.id as space_id,
                              case when spaces.company_seller_id = space_users.user_company_id and logs.created_at between '".$filters['old_date']."' and '".$filters['date']."' and (logs.content_type = 'App\PostMedia' or logs.content_type = 'AppPostMedia' or logs.action = 'view embedded url')
                                  then 1
                                  else 0
                              end as seller_total,
                              case when spaces.company_buyer_id = space_users.user_company_id and logs.created_at between '".$filters['old_date']."' and '".$filters['date']."' and (logs.content_type = 'App\PostMedia' or logs.content_type = 'AppPostMedia' or logs.action = 'view embedded url')
                                  then 1
                                  else 0
                              end as buyer_total
                              from activity_logs as logs
                              right join spaces on spaces.id::text = logs.space_id
                              left join space_users on space_users.user_id = logs.user_id and space_users.space_id::text = logs.space_id
                              where spaces.deleted_at is null ".$filter_where."
                            ) as tbl
                          group by id $orderby $limit_offset");

        return arrayValueToKey(objectToArray($posts_interactions), 'id');
    }

    public static function getAllShareNps($supplier_id, $buyer_id, $filters) {
      $order_by = $offset_value ='';
      $where = 'WHERE spaces.deleted_at is null';
      $limit_offset = '';

      if(!empty(array_filter($filters['status_filter']))){
          $where = $where." and spaces.status IN ('".implode("','",$filters['status_filter'])."')";
      } 

      $rag_filter = self::getRagFilter($filters);
      if(is_array($rag_filter) && sizeOfCustom($rag_filter)){
        $rag_filter = " and spaces.id in('" . implode("', '", $rag_filter) . "')";
      } else $rag_filter = '';

      if(empty($filters['spaces']) || $filters['sort'] == 'nps'){
          $space_where = (!empty($supplier_id))?"spaces.company_seller_id IN ('".$supplier_id."')":"";
          $buyer_where = (!empty($buyer_id))?"spaces.company_buyer_id IN ('".$buyer_id."')":"";
          $filter = $where;
          if(!empty($supplier_id) && !empty($buyer_id)){
            $filter = $where.' and '.$space_where.' or spaces.deleted_at is null and '.$buyer_where;
          }else if(!empty($supplier_id) && empty($buyer_id)){
            $filter = $where.' and '.$space_where;
          }else if(empty($supplier_id) && !empty($buyer_id)){
            $filter = $where.' and '.$buyer_where;
          }
          $order_by = (trim($filters['sort']) != 'nps')?"":"order by total ".$filters['sort_order'].", feedback_status ".$filters['sort_order'];
          $offset_value = "OFFSET ".$filters['offset'];
          $limit_offset = !$filters['disable_offset'] ? "LIMIT ".$filters['limit']." ".$offset_value:'';
          
      }else{
          $filter = (!empty($filters['spaces']))?$where." and spaces.id IN ('".implode("','",$filters['spaces'])."')":"";
      }

          $where_date = (!empty($filters['date_value']))?" and feed.created_at <= '".$filters['date_value']."'":'';
          $nps = DB::select("SELECT id,feedback_status,
                             case when (SUM(good) + SUM(medium) + SUM(bad)) > 0 
                             then ((SUM(good)*100)/(SUM(good) + SUM(medium) + SUM(bad)))-((SUM(bad)*100)/(SUM(good) + SUM(medium) + SUM(bad)))
                             when (SUM(good) + SUM(medium) + SUM(bad)) = 0 and feedback_status= FALSE
                             then -500
                             else 0 end as total
                             from (select
                                    spaces.id,spaces.feedback_status,
                                    case when feed.rating::int >= 9 $where_date
                                        then 1
                                        else 0
                                    end as good,
                                    case when feed.rating::int < 9 and feed.rating::int > 6 $where_date
                                        then 1
                                        else 0
                                    end as medium,
                                    case when feed.rating::int < 6 $where_date
                                        then 1
                                        else 0
                                    end as bad
                                from feedback as feed
                                right join spaces on spaces.id = feed.space_id
                                ".$filter." ".$rag_filter."
                          ) As tbl
                          group by id,feedback_status
                          ".$order_by." ".$limit_offset);
          return arrayValueToKey(objectToArray($nps), 'id');
    }

    public static function getRagFilter($filters, $wrap_with_and=true){
      $RAG_filter = [];
      if(isset($filters['RAG_filter']) && sizeOfCustom(array_filter($filters['RAG_filter']))){
        foreach ($filters['RAG_filter'] as $filter) {
          if($filter == ManagementInformation::RAG_FILTER_LABEL['l1'])
            $RAG_filter[] = "max(logs.created_at) between now() - interval '7 days' and now()";
          elseif($filter == ManagementInformation::RAG_FILTER_LABEL['l2'])
            $RAG_filter[] = "max(logs.created_at) BETWEEN Now() - interval '13 days' AND now() - interval '8 days'";
          elseif($filter == ManagementInformation::RAG_FILTER_LABEL['l3'])
            $RAG_filter[] = "(max(logs.created_at) < now() - interval '14 days' or max(logs.created_at) is null)";
        }
        if(sizeOfCustom($RAG_filter)>1){
          $RAG_filter = '('.implode(" or ", $RAG_filter).')';
        } else if(isset($RAG_filter[0])){
          $RAG_filter = $RAG_filter[0];
        } else{
          $RAG_filter = [config('constants.DUMMY_UUID.0')];
        }
        if($RAG_filter){
          $RAG_filter = DB::select("
            SELECT spaces.id from management_information_email_logs logs
            right join spaces on spaces.id = logs.space_id
            group by spaces.id
            HAVING {$RAG_filter}
          ");
          $RAG_filter = array_column($RAG_filter, 'id');
        } 
        if(!sizeOfCustom($RAG_filter)) {
          $RAG_filter = [config('constants.DUMMY_UUID.0')];
        }
      } else {
        $RAG_filter='';
      }
      return $RAG_filter;
    }

    public static function ManagementInformationSpacesData($spaces){
      $spaces = DB::select("
        SELECT spaces.id, max(logs.created_at) as mail_log from management_information_email_logs logs
        right join spaces on spaces.id = logs.space_id
        where spaces.id in ('".implode("','", $spaces)."')
        group by spaces.id
      ");
      return arrayValueToKey(objectToArray($spaces), 'id');
    }

    public static function ManagementInformationAdminUserData($spaces){
      $spaces = DB::select("
        SELECT spaces.id, concat(users.first_name, ' ', users.last_name) AS FIRSTNAME from users
        left join space_users on space_users.user_id = users.id
        left join spaces on spaces.id = space_users.space_id
        where spaces.id in ('".implode("','", $spaces)."') and space_users.user_type_id=2 and space_users.deleted_at is null
        group by spaces.id,users.first_name,users.last_name
      ");
      return arrayReduce(objectToArray($spaces));
    }

    public function getAllShareProgressBar($supplier_id, $buyer_id, $filters) {
      $order_by = $offset_value ='';
      $where = 'WHERE sps.deleted_at is null';
      $limit_offset = '';

      $rag_filter = self::getRagFilter($filters);
      if(is_array($rag_filter) && sizeOfCustom($rag_filter))
          $rag_filter = " and sps.id in('" . implode("', '", $rag_filter) . "')";
      else
          $rag_filter = '';

      if(!empty(array_filter($filters['status_filter'])))
          $where = $where." and sps.status in ('".implode("','", $filters['status_filter'])."')";
      

      if(empty($filters['spaces']) || $filters['sort'] == 'progress_bar'){
          $space_where = (!empty($supplier_id))?"sps.company_seller_id IN ('".$supplier_id."')":"";
          $buyer_where = (!empty($buyer_id))?"sps.company_buyer_id IN ('".$buyer_id."')":"";
          $filter = $where;
          if(!empty($supplier_id) && !empty($buyer_id))
              $filter = $where.' and '.$space_where.' or sps.deleted_at is null and '.$buyer_where;
          else if(!empty($supplier_id) && empty($buyer_id))
              $filter = $where.' and '.$space_where;
          else if(empty($supplier_id) && !empty($buyer_id))
              $filter = $where.' and '.$buyer_where;
          
          $order_by = (trim($filters['sort']) != 'progress_bar')?"":"order by total ".$filters['sort_order'];
          $offset_value = "OFFSET {$filters['offset']}";
      }else{
          $filter = (!empty($filters['spaces']))?$where." and sps.id IN ('".implode("','",$filters['spaces'])."')":"";
      }

      $limit_offset = !$filters['disable_offset'] ? "LIMIT {$filters['limit']} {$offset_value}":'';
      $where_date = (!empty($filters['date_value']))?" and sps.created_at <= '".$filters['date_value']."'":'';
      $progress_bar = DB::select("Select *, logo+banner+category+executive_summary+links+twitter+domain+posts as total from (SELECT sps.id,
                          case when sps.seller_logo is not null and sps.buyer_logo is not null then 1 else 0 end as logo,
                          case when sps.background_image is not null then 1 else 0 end as banner,
                          case when (SELECT count(*) FROM (SELECT jsonb_object_keys(category_tags::jsonb)) v) >= 6 then 1 else 0 end as category,
                          case when sps.executive_summary != '' then 1 else 0 end as executive_summary,
                          case when sps.twitter_handles::text != '".config('constants.EMPTY_JSON')."' then 1 else 0 end as twitter,
                          case when sps.domain_restriction is false or sps.metadata::text != '".config('constants.EMPTY_JSON')."' then 1 else 0 end as domain,
                          case when count(distinct pst.id) >= 5 then 1 else 0 end as posts,
                          case when count(distinct ql.id) >= 2 then 1 else 0 end as links
                          from spaces as sps
                          left join posts pst on pst.space_id = sps.id
                          left join quick_links ql on ql.share_id = sps.id
                          ".$filter." ".$rag_filter." group by sps.id) as tbl
                          $order_by $limit_offset");
      return arrayValueToKey(objectToArray($progress_bar), 'id');

      
    }
}