<?php

namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class PostViews extends Model {
    protected $table = 'post_views';
    protected $fillable = ['space_id','user_id','post_id'];

    public function User(){
    	return $this->belongsTo("App\User", "user_id"); 
    }

    public static function logSingleView($row_data){
    	return static::firstOrCreate($row_data);
    }
    public static function postViewData($space_id,$start_date,$end_date){
    	return DB::select(
			"SELECT
				to_char(vew.created_at, 'YYYY-MM'),
				case when user_company_id = ( select company_buyer_id from spaces where id = '".$space_id."' )
					then 'Buyer'
				when user_company_id = ( select company_seller_id from spaces where id = '".$space_id."')
					then 'Seller'
				end as tag, count(*)
			from post_views vew
			inner join posts post on post.id = vew.post_id
			inner join space_users su on post.space_id = su.space_id and vew.user_id = su.user_id
			where su.space_id = '".$space_id."'
			and user_company_id != '00000000-0000-0000-0000-000000000000'
			and vew.created_at between  '".$start_date."' and  '".$end_date."'
			group by tag,to_char(vew.created_at, 'YYYY-MM')
			order by to_char(vew.created_at, 'YYYY-MM');"
		);
    }

    public function saveViewPost($space_id, $post_id, $user_id) {
    	$this->user_id = $user_id;
        $this->space_id = $space_id;
        $this->post_id = $post_id;
        $this->save();
    }

    public function postViewerList($post_id) {
        return $this->with('User')
          ->where('post_id', $post_id)
          ->selectRaw('distinct user_id')
          ->get()->toArray();
    }
}