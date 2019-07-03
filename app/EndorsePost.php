<?php

namespace App;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use DB;
class EndorsePost extends Model
{
	//use SoftDeletes;
	//protected $dates = ['deleted_at'];
    protected $keyType = 'string';
    public function getIdAttribute($value){
    	return (string) $value;
    }
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function space_user()
    {
        return $this->belongsTo('App\SpaceUser', "user_id","user_id");
    }
    public static function likePost($space_id,$start_date,$end_date){
    return DB::select(
            "SELECT
                to_char(lke.created_at, 'YYYY-MM'),
                case when user_company_id = ( select company_buyer_id from spaces where id = '".$space_id."' )
                    then 'Buyer'
                when user_company_id = ( select company_seller_id from spaces where id = '".$space_id."')
                    then 'Seller'
                end as tag, count(*)
            from endorse_posts lke
            inner join posts post on post.id = lke.post_id
            inner join space_users su on post.space_id = su.space_id and lke.user_id = su.user_id
            where su.space_id = '".$space_id."'
            and user_company_id != '00000000-0000-0000-0000-000000000000'
            and lke.created_at between  '".$start_date."' and  '".$end_date."'
            and post.deleted_at is null
            group by tag,to_char(lke.created_at, 'YYYY-MM')
            order by to_char(lke.created_at, 'YYYY-MM');"
        );
  }

  public function getLikePost($post_id){
    return $this->select('user_id')->where('post_id', $post_id)->orderBy('created_at', 'desc')->first();
  }
  public function getUsersLikedPost($post_id){
    return self::select('user_id')->where('post_id', $post_id)->orderBy('created_at', 'desc')->get()->toArray();
  }
}
