<?php

namespace App;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Post as PostHelper;
use App\PostMedia;

class PostMedia extends Model {
	protected $casts = [
		'metadata' => 'json',
		's3_file_path' => 'JSON'
	];
	protected $fillable = ['post_id', 'metadata', 's3_file_path', 'post_file_url'];
	protected $appends = ['created_at_formatted', 'post_file_url'];
    protected $hidden = ['post_file_url_old'];
	const FILE_FILTERS = [
		'doc' => ['xls', 'csv', 'xlsx', 'docx', 'ppt', 'pptx', 'doc'],
		'pdf' => ['pdf'],
		'img' => ['jpg','jpeg','png'],
		'vid' => ['mp4','mov','MOV'],
		'url' => ['url']
	];

	public function getS3FilePathAttribute($file_path) {
        return composeFilePath(json_decode($file_path, true));
    }

	public static function PostFile($file_name, $selection_method='first'){
		return PostMedia::whereRaw("s3_file_path->>'file' ilike '%".$file_name."'")->$selection_method();
	}


	public function getCreatedAtFormattedAttribute(){
		return Carbon::parse($this->created_at)->format('d/m/Y');
	}
	
    public function getPostFileUrlAttribute(){
        return wrapUrl(composeUrl('/'.$this->s3_file_path, false));
    }

	public static function postFiles($request, $login_user){
		parse_str(urldecode($request['filters']??''), $filtered_data);
        $request['filters'] = $filtered_data;

		$file_type_filter = (new PostHelper)->postFileTypeFilter($request);
		$added_by = isset($request['filters']['users']) ? "and posted_by in ('".implode("','", $request['filters']['users'])."')":'';

		$category = isset($request['filters']['catgories']) ? "and category_id in ('".implode("','", $request['filters']['catgories'])."')":'';

		$date_range = isset($request['filters']['date_range']) && strlen($request['filters']['date_range'])? explode("-", $request['filters']['date_range']):'';
		$date_range = $date_range ? "and created_at between '".$date_range[0]."' and '".$date_range[1]."'::date + INTERVAL '1 day'":'';

		$selection = "sp.category_tags->>(pst.metadata->>'category') as category,
			pst.post_subject, usr.first_name||' '||usr.last_name as user_name,
			pst.id as post_id, pst.space_id as space_id, visibility,
			pst.user_id as posted_by,pst.metadata->>'category' as category_id";
				
	 $files = DB::select(
			"SELECT * from(SELECT
				substring(pm.metadata->>'originalName' from '(.+?)(\.[^.]*$|$)') as file_name, 
				substring(pm.metadata->>'originalName' from '\.([^\.]*)$') as file_extention,
				pm.metadata->>'size' as file_size,
				pm.s3_file_path as file_url, 
				pm.metadata->>'originalName' as post_file_name,
				pst.metadata->'get_url_data' as post_embeded_url,
				pst.created_at as created_at, 
				".$selection."
			from post_media pm 
			inner join posts pst on pst.id = pm.post_id
			inner join spaces sp on sp.id = space_id
			inner join users usr on usr.id = pst.user_id
			where 
				pst.space_id ='". $request['space_id']."'
				and pst.deleted_at is null

			union all 
			
			SELECT 
				pst.metadata->'get_url_data'->>'url' as file_name, 
				'URL' as file_extention,
                '' as file_size,
				to_json(pst.metadata->'get_url_data'->>'url') as file_url, 
				'file_name' as post_file_name,
				pst.metadata->'get_url_data' as post_embeded_url,
				pst.created_at as created_at, 
				".$selection."
				
			from posts pst 
			inner join users usr on usr.id = pst.user_id
			inner join spaces sp on sp.id = space_id
			where 
				pst.space_id ='". $request['space_id']."'
				and pst.metadata->'get_url_data' is not null
				and pst.deleted_at is null

			union all

			SELECT 
			    substring(cmt_atch.file_name from '(.+?)(\.[^.]*$|$)') as file_name, 
			    substring(cmt_atch.file_name from '\.([^\.]*)$') as file_extention, 
                cmt_atch.metadata->>'size' as file_size,
				cmt_atch.s3_file_path as file_url, 
				cmt_atch.file_name as post_file_name,
				pst.metadata->'get_url_data' as post_embeded_url,
				cmt_atch.created_at as created_at, 
				".$selection."
				
			from posts pst 
			inner join comments cmt on cmt.post_id = pst.id
			inner join comment_attachments cmt_atch on cmt_atch.comment_id = cmt.id
			inner join spaces sp on sp.id = space_id
			inner join users usr on usr.id = cmt.user_id

			where 
				pst.space_id ='". $request['space_id']."'
				and pst.deleted_at is null) as result

			where
				( post_file_name is not null or post_embeded_url is not null)
				and file_name ilike '%".($request['filters']['file_name']??'')."%' 
				and post_subject ilike '%".($request['filters']['post_subject']??'')."%'
				and (visibility ilike '%all%' or visibility ilike '%".$login_user."%')
				$added_by
				$file_type_filter
				$category
				$date_range

			
			order by ".($request['order_by']??1).' '.($request['order']??1)." 
			limit ".$request['limit']." offset ".($request['offset']??0)
		);

		 foreach ($files as $key => $file) {
		 	$files[$key]->file_url = filePathJsonToUrl($file->file_url);
		 }

		 return $files;
	}

	public function post() {
		return $this->belongsTo('App\Post');
	}

	public static function Attachments($space_id, $start_date, $end_date){
        return DB::select(
			"SELECT
				to_char(pm.created_at, 'YYYY-MM'),
				case when user_company_id = ( select company_buyer_id from spaces where id = '".$space_id."' )
					then 'Buyer'
				when user_company_id = ( select company_seller_id from spaces where id = '".$space_id."')
					then 'Seller'
				end as tag, count(*)
			from post_media pm
			inner join posts post on post.id = pm.post_id
			inner join space_users su on post.space_id = su.space_id and post.user_id = su.user_id
			where su.space_id = '".$space_id."'
			and user_company_id != '00000000-0000-0000-0000-000000000000'
			and pm.created_at between  '".$start_date."' and  '".$end_date."'
			group by tag,to_char(pm.created_at, 'YYYY-MM')
			order by to_char(pm.created_at, 'YYYY-MM');"
		);
    }

}
