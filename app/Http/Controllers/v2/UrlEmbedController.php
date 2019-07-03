<?php

namespace App\Http\Controllers\v2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Generic as GenericTrait;

class UrlEmbedController extends Controller
{
	use GenericTrait;

    public function getUrlData($url_for_preview="") 
    {
	    if(Request()->q){
	      $url_for_preview=Request()->q;
	    }
	    $url_string = $url_for_preview;
	    $regex = config('constants.email.regex');
	    preg_match($regex, $url_for_preview, $match, PREG_OFFSET_CAPTURE, 0);
	    
	    if(!isset($match[0]) || !sizeOfCustom($match[0]))
	      return 0;
	    if( sizeOfCustom($match[0]) ){ 
	      $url_temp[$match[0][0]] = strpos($url_string, $match[0][0]);      
	      $url_list = implode(", ", $match[0]);
	    }
	    $full_url = $url_for_preview = array_keys($url_temp, min($url_temp))[0];

	    $content = $this->custom_curl([
	      'url' => config('constants.EMBEDLY_API_URL').'?url='.trim($url_for_preview).'&key='.env('URL_PRE'),
	      'timeout_seconds' => 4,
	      'request_type' => 'GET'
	    ]);
	    if( !isset($content) || !$content ) return 0;
	    $data  = json_decode($content, true);
	    if( isset($data['error_code']) ) return 0;
	    if( ( !isset($data['title']) || !isset($data['description']))) return 0;

	    $data['thumbnail_url'] = $data['images'][0]['url']??env('APP_URL').'/images/video-poster.jpg';
	    $res['domain']        = $this->getDomain($url_for_preview);    
	    $res['favicon']       = $data['thumbnail_url'];
	    $res['title']         = $data['title'];
	    $res['description']   = $data['description']??'';
	    $res['thumbnail_img'] = isset($data['thumbnail_url'])? 1:0;
	    $res['full_url']      = $data['url']??'';
	    $res['url']           = $data['url']??'';
	    $res['url_list']      = $url_list;
	    $res['api_response']  = $data;
	    $res['metatags'] = '';
	    if( (is_numeric(strpos($url_for_preview, 'youtube')) && is_numeric(strpos($url_for_preview, 'watch'))) ) {
	      $url_data = parse_url($url_for_preview);
	      parse_str($url_data['query'],$query);

	      if(isset($query['v'])){
	        $res['metatags'] = array('twitter:player' => config('constants.URL.youtube_embed').$query['v'] );
	      }
	    }
	    return $res;
  	} 

  	public function getDomain($url) 
  	{
	    $pieces = parse_url($url);
	    $domain = isset($pieces['host']) ? $pieces['host'] : '';
	    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
	      return $regs['domain'];
	    }
	    return false;
  	}
}