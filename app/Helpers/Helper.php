<?php

use App\{Company, Space,SpaceUser, User, OTP};
use App\Helpers\{Aws, Logger};
use Intervention\Image\ImageManagerStatic as Image;

function checkLink($string){
  if(filter_var($string, FILTER_VALIDATE_URL) === FALSE){
    return false;
  }else{
    return true;
  }
}

function limitAlertString($text, $maxchar, $end='...') {
  $output = $text;
  if (strlen($text) > $maxchar || $text == '') {
    $words = preg_split('/\s/', $text);
    $output = '';
    $counter = 0;
    foreach ($words as $key => $value) {
      $is_link = false;
      if(checkLink($words[$counter])){
        $is_link = $words[$counter];
        $words[$counter] = ' link';
      }

      $length = strlen($output)+strlen($words[$counter]);
      if ($length > $maxchar) {
        $output .= " " . ($is_link?linkToTest($is_link, $is_link):$words[$counter]);
        break;
      }
      $output .= " " . ($is_link?linkToTest($is_link, $is_link):$words[$counter]);
      ++$counter;
    }
    $output .= $end;
    } else {
      return linkToTest($output, env('APP_URL'));
    }
}

function apiResponse($data=[], $code=200, $message=[]){
  return response(compact('code', 'message', 'data'), $code);
}

function apiResponseComposer($code=200, $message=[], $data=[]){
  return response(compact('code', 'message', 'data'), $code);
}

function urlCleaner($url) {
  return str_replace(' ', config('constants.SPACE_ENCODING'), $url);
}

function getRealIpAddr() {
  
  if (!empty($_SERVER['HTTP_CLIENT_IP'])) {  //check ip from share internet    
    $ip=$_SERVER['HTTP_CLIENT_IP'];
  
  } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {//to check ip is pass from proxy
    $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
  
  } else {
    $ip=$_SERVER['REMOTE_ADDR'];
  }
  return $ip;
}

function sizeOfCustom($array_var) {
  if(is_object($array_var)) {
   $array_var = objectToArray($array_var);
  }

  return is_array($array_var) ? sizeof($array_var) : false;
}

function wrapUrl($url){
  if($url && strpos($url, config('constants.LINKED_IN_URL')) == false)
    return env('APP_URL')."/display_assert?file_path=".$url."&token=".mb_substr(date("mdhi"), 0, -1);
  if($url && strpos($url, config('constants.LINKED_IN_URL')) !== false)
    return $url;
  return false;
}

function composeEmailUrl($file_path){  
  if(stripos($file_path, env('APP_URL')) === 0)
    return $file_path;
  $file_path = str_replace(config('constants.s3.url').env('S3_BUCKET_NAME'), '', $file_path);
  $otp = OTP::searchByUrl($file_path);
  if(!$otp)
    $otp = OTP::create([
      'app_url'=> $file_path,
      'method'=> 'get',
      'metadata'=> ['file_path' => $file_path]
    ]);
  return env('APP_URL').'/email_attachment/'.$otp->id.'?q='.rand();
}

function composeFilePath($file_info){
  if (empty($file_info) || !$file_info || !is_array($file_info) || !isset($file_info['path']))
        return '';
  $file_info['path'] = implode('/', $file_info['path']);
  $file_info['path'] = $file_info['path'] ? $file_info['path'] . '/' : '';
  return "{$file_info['path']}{$file_info['file']}";
}

function checkBuyerSeller($space_id, $user_id){
    $space_user = SpaceUser::getActiveSpaceUser($space_id, $user_id);
    if(!sizeOfCustom($space_user)) return '';
    $seller = (new Space)->getCompanySeller($space_id, $space_user[0]['user_company_id']);
    $buyer = (new Space)->getCompanyBuyer($space_id, $space_user[0]['user_company_id']);
    if(sizeOfCustom($seller)) return 'seller';
    if(sizeOfCustom($buyer)) return 'buyer';
    return '';
}

function composeUrl($file_path, $json=true){
  if(empty($file_path) || !$file_path) 
    return '';

  try{
    $region_prefix = env('AWS_REGION_PREFIX');
    $region = env('AWS_REGION');
    $s3_bucket = env('S3_BUCKET_NAME');
    if($json)
    {  
      if(isset($file_path['path']) && isset($file_path['file']))
      {
        $file_info = getAwsFileName($file_path['path'], $file_path['file']);
        if(trim($file_info) == '' || empty($file_info))
            return '';
      } else { 
        $file_data = json_decode($file_path);
        if(!isset($file_data->file)){
           if(strpos($file_path, env('APP_URL')) !== false)
               return $file_path;
           else if(strpos($file_path, env('APP_URL')) == false)
               return "https://{$region_prefix}{$region}.amazonaws.com/{$s3_bucket}/$file_path";
        }
        $file_info = $file_data->file;
        if(!empty($file_data->path)){ 
          $file_info = getAwsFileName($file_data->path, $file_data->file);
          if(trim($file_info) == '' || empty($file_info))
              return '';
        }
      }
      if(strpos($file_info, config('constants.LINKED_IN_URL')) !== false)
        return $file_info;

      return "https://{$region_prefix}{$region}.amazonaws.com/{$s3_bucket}/$file_info";
    } else {
      if(strpos($file_path, config('constants.LINKED_IN_URL')) !== false || is_numeric(stripos($file_path, env('S3_BUCKET_NAME'))))
        return $file_path;
      return "https://{$region_prefix}{$region}.amazonaws.com/{$s3_bucket}{$file_path}";
    }
  } catch(\Exception $e) {
    show($e->getMessage());
  }

}

function getAwsFileName($file_path, $file_name) {
    $file_path = implode('/', $file_path);
    $file_path = $file_path ? $file_path.'/':'';
    if(trim($file_name) == '' || empty($file_name))
        return '';

    return $file_path.$file_name;
}

function createAndUploadVideoScreentshot($video_url){
  try {
    $thumbnail = createVideoThumbnail($video_url);
  } catch (Exception $exception) {
    (new Logger)->log([
      'action' => trans('messages.error.video_thumbnail'),
      'description' => $exception->getMessage()
    ]);
    $thumbnail = file_get_contents(env('APP_URL').'/images/player_layer.png');
  }
  
  $file_data = [
    'folder' => '/post_alert_video_thumbnail/',
    'file_name' => time().'.png',
    's3_url' => config('constants.s3.url'),
    'file_content' => $thumbnail
  ];
  return uploadFileOnS3($file_data);
}

function createVideoThumbnail($url){
  $thumbnail = time().'.png';
  $ffmpeg = \FFMpeg\FFMpeg::create();
  $ffprobe = FFMpeg\FFProbe::create();
  $duration = $ffprobe
    ->streams($url)
    ->videos()                   
    ->first()                  
    ->get('duration');

  $video = $ffmpeg->open($url);
  $frame = $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(ceil($duration/2)));
  $frame->save($thumbnail);
  return file_get_contents($thumbnail);
}

function uploadFileOnS3(array $file_data){
  $s3 = \Storage::disk('s3');
  $s3_bucket = env("S3_BUCKET_NAME");
  
  $file_path = $file_data['folder'].$file_data['file_name'];
  $full_url = $file_data['s3_url'].$s3_bucket.$file_path;

  $s3->put($file_path, $file_data['file_content'], 'public');
  return $full_url;
}

function getDownloadableAwsSignedURL($url, $file_name){
  $aws = (new Aws);
  $url = $aws->breakURL($url);
  return $aws->requestSignedUrl($url, 5, 'attachment', $file_name);
}

function getAwsSignedURL($url, $full_url=true){
  return (new Aws)->getAwsSignedURL($url, $full_url);
}

function spaceSessionData($space_id){
  $session_data = Space::findOrFail($space_id);
  $session_data['is_admin'] = SpaceUser::isAdmin($space_id, Auth::user()->id);
  return $session_data;
}

function objectToArray($object){
  return json_decode(json_encode($object), true);
}

function filterArray($data_array, $target_key){

  array_filter($data_array, function($value, $key) use(&$data_array, $target_key){
    if(!$value[$target_key])
      unset($data_array[$key]);
  }, ARRAY_FILTER_USE_BOTH);

  return array_values($data_array);
}

function arrayValueToKey($array, $key) {
  return array_combine(array_column($array, $key), $array);
}

function arrayReduce($input) {
  return $output = array_reduce(
    $input,
    function (array $carry, array $item) {
        $key = $item['id'];
        if (isset($carry[$key])) {
            $carry[$key]['firstname'] .= ', '.$item['firstname'];
        } else {
            $carry[$key] = $item;
        }
        return $carry;
    },
    array()
);
}

function getAwsValidUrl($url){
   return (new App\Http\Controllers\PostController)->getAwsValidUrl($url);
}

function getFileName($url, $orignal_name){
     $path = parse_url($url, PHP_URL_PATH);
     $file_name = explode('.', $orignal_name);
     array_pop($file_name);
     return $file_name = implode('.', $file_name);
} 

function getFavicon($url){
  return config('constants.URL.google_favicon').$url;
}

function getCompanyName($company_id){
  $company = Company::findOrFail($company_id);
  return $company['company_name'];
}

function checkUuidFormat($string) {
    $uuid_format = '/^\{?[A-Za-z0-9]{8}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{12}\}?$/';
    if (preg_match($uuid_format, $string)) {
      return true;
    }
    return false;
}

function generateImageThumbnail($source_image_path, $width, $height, $change_acl = true, $dimension_required=false) {
  try{
    if($change_acl) makeAWSFilePublic($source_image_path);    
    list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
    switch ($source_image_type) {
        case IMAGETYPE_GIF:
            $source_gd_image = imagecreatefromgif($source_image_path);
            break;
        case IMAGETYPE_JPEG:
            $source_gd_image = imagecreatefromjpeg($source_image_path);
            break;
        case IMAGETYPE_PNG:
            $source_gd_image = imagecreatefrompng($source_image_path);
            break;
    }
    if ($source_gd_image === false) {
      if($dimension_required) $dimensions = compact('thumbnail_image_width', 'thumbnail_image_height');
      else $dimensions = 'width='.$width.' height='.$height;
      return $dimensions;
    }
    $source_aspect_ratio = $source_image_width / $source_image_height;
    $thumbnail_aspect_ratio = $width/$height;
    if ($source_image_width <= $width && $source_image_height <= $height) {
      $thumbnail_image_width = $source_image_width;
      $thumbnail_image_height = $source_image_height;
    } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
      $thumbnail_image_width = (int) ($height * $source_aspect_ratio);
      $thumbnail_image_height = $height;
    } else {
      $thumbnail_image_width = $width;
      $thumbnail_image_height = (int) ($width / $source_aspect_ratio);
    }
    if($dimension_required) $dimensions = compact('thumbnail_image_width', 'thumbnail_image_height');
    else $dimensions = 'width='.$thumbnail_image_width.' height='.$thumbnail_image_height;
  } catch(\Exception $e){
    if($dimension_required) $dimensions = compact('thumbnail_image_width', 'thumbnail_image_height');
    else $dimensions = 'width='.$width.' height='.$height;
  }
  return $dimensions;
}

function fileIcon($file_name){
  $file_name = explode('.', $file_name);
  return env('APP_URL').config('constants.extension_wise_png_image.'.strtolower(array_pop($file_name)));
}

function makeAWSFilePublic($url){
  if(is_numeric(stripos($url, env('S3_BUCKET_NAME'))))
    (new AWS)->change_visibility($url, 'public');
  return $url;
}

function anyImage($search_array) {
  $found = 0;
  foreach ($search_array as $key => $row) {
      if(is_numeric(stripos($row['metadata']['mimeType'], 'image')))
        $found++;
  }
  return $found;
}

function anyVideo($search_array, $index_of = false) {
  $found = 0;
  foreach ($search_array as $key => $row) {
    if(is_numeric(stripos($row['metadata']['mimeType'], 'video'))){
      if($index_of) {
        $found = ($key+1);
        break;
      }
      $found++;
    }
  }
  return $found;
}

function checkSeeMoreEligiblity($comment){
  return (substr_count($comment, '<br>') > 2 || strlen(strip_tags($comment))>config('constants.post_comment_string_limit'));
}

function formatCommentText(string $comment_text) {
  
    $regex_for_link = array('`((?:https?|ftp)://\S+[[:alnum:]]/?)`si','`((?<!//)(www\.\S+[[:alnum:]]/?))`si'); 
    $regex_replacement = array('<a class="post_emb_link" href="$1" target="_blank">$1</a>', '<a class="post_emb_link" href="http://$1" target="_blank">$1</a>');

    $comment_after_process = str_replace('</div>', '', trim($comment_text));
    $comment_after_process = str_replace('<div>', ' <br>', $comment_after_process);
    $comment_after_process = str_replace('<br>', ' <br>', $comment_after_process);
    $comment_after_process = strip_tags(trim($comment_after_process), '<a><br>');
    
    $comment_after_process = preg_replace($regex_for_link, $regex_replacement, $comment_after_process);
    return ['comment_after_process'=>$comment_after_process, 'raw_comment' => $comment_text];
}

function removeSpecialCharacters(string $string) {
   $string = str_replace(' ', '-', $string);
   return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

function limitString($text, $maxchar, $end='...') {
  $output = $text;
  if (strlen($text) > $maxchar || $text == '') {
    $words = preg_split('/\s/', $text);      
    $output = '';
    $counter = 0;
    while (true) {
      $length = strlen($output)+strlen($words[$counter]);
       if ($length > $maxchar) {
                $output .= " " . $words[$counter];
            break;
       }
        $output .= " " . $words[$counter];
        ++$counter;
    }
    $output .= $end;
    } 
  return $output;
}

function linkToTest(string $string, string $link, string $link_wrap='see more') {
  $regex = config('constants.email.regex');
  $link_html = "<a href='$link'>$link_wrap</a>";
  return preg_replace($regex, $link_html, $string);
}

function getPostThumbnailUrl( $url, $domain ){
  if(!$url) return $domain.'/favicon.ico';
  if(is_numeric(strpos($url, 'https://'))) return $url;
  else return env('APP_URL').'/file_loading?url='.$url;
}

function linkMentionUser($message){
  return $message; //this is temp changes for production will revert back after production push, please ignore while code reviewing. Thanks
  $users = implode("%','", regExtract($message, config('constants.email.mention'))[0]);
  $user_list = str_replace('@', '', $users);

  $user_list = User::taggedUsers($user_list);

  foreach ($user_list as $user ) {
    $user['fullname'] = "<a data-id='".$user['id']."'onclick='liked_info(this);'>".$user['fullname']."</a>";
    $message = str_replace($user['username'], ucfirst($user['fullname']), $message);
  }
  return $message;
}

function regExtract($string, $regex) {
  preg_match_all($regex, $string, $match, false);
  return $match;
}

function show($variable){
  print_r($variable); die();
}

function resizeImage($image, $width=null, $height=null, $crop=false, $file_location=null ){
    if(!$image)
      return false;
    $image = explode('file_path=',$image);
    if(isset($image[1]))
      $image = urldecode($image[1]);
    else
      $image = urldecode($image[0]);
    $explode_url = explode('&',$image);
    $image = $explode_url[0];
    $file_name = time().'_'.rand();
    $file_name = $file_name.".png";
    $s3 = \Storage::disk('s3');
    $s3_bucket = env("S3_BUCKET_NAME");
    $filePath = $file_location??('/company_logo/'.$file_name);
    $full_url = config('constants.s3.url').$s3_bucket."".$filePath;
    if(!is_exists($full_url)){ 
      $image = getAwsSignedURL($image); 
      if(!$image) 
        abort(404);
      $base_image = Image::make(str_replace(' ', '%20', $image));
      if(!$crop)
      {
        $base_image->resize($width, $height)->encode('data-url');
      } else {
          $rwidth = $width;
          $rheight = $height;
          ($width > $height)? $rwidth = null : $rheight = null;
          $base_image->resize($rwidth, $rheight, function ($constraint) {
              $constraint->aspectRatio();
          })
          ->crop($width, $height)
          ->encode('data-url');
      }
      $s3->put($filePath, file_get_contents($base_image), 'public');
    }
    return $full_url;
}

function is_exists($file){
  if(empty($file))
  $file_content ='';
  try{
      $file_content = file_get_contents($file);
    }catch (Exception $e){
      return false;
   }
  return  empty($file_content) ? false : true;
}

function makeAndResizeImage($image_url, $x_axis, $y_axis) {
  $image = Image::make($image_url);
  return $image->resize($x_axis, $y_axis);
}

function makeImageRound($logo, $dimension){
  $image = Image::make($logo);
  
  $image->resize($dimension['logo_resize_x'],$dimension['logo_resize_y'], function ($constraint) {
    $constraint->upsize();
  });

  $image->fit($dimension['logo_resize_x']);
  
  
  $width = $image->getWidth();
  $height = $image->getHeight();        
  $canvas_size = $width<=$height?$width:$height;
  $mask = \Image::canvas($canvas_size, $canvas_size);
  
  
  $mask->circle($dimension['logo_resize_x'], 0, 0, function ($draw) {
    $draw->background('#fff');
    $draw->border(1,'#FA0505');
  });
  return $data = $image->mask(getAwsSignedURL(composeUrl("/1488362727.png", false)), true)->encode('data-url');
}

function wrapCircularBorder($left_logo, $file_name, $folder, $dimension) {
  ini_set('memory_limit', -1); // @reviewer: This is for testing purpose and will be removed, you can ignore this :P   
  $left_logo = makeImageRound($left_logo, $dimension);
  $left_logo = mergeLogo(env('APP_URL').'/images/oval.png', $left_logo, $dimension['logo_resize_x'], $dimension['logo_resize_y']);

  $file_data = [
    'folder' => '/company_logo/'.$folder.'/',
    'file_name' => $file_name.'.png',
    's3_url' => config('constants.s3.url'),
    'file_content' => file_get_contents($left_logo)
  ];
  return uploadFileOnS3($file_data);
}

function mergeLogo($base_image, $top_image, $offset_x=0, $offset_y=0, $position='center') {
  $base_image = makeAndResizeImage($base_image, $offset_x+1, $offset_y+1);
  return $base_image->insert($top_image, $position, $offset_x, $offset_y)->encode('data-url');
}

function mergeImages($base_image, $top_image, $position='center', $offset_x=0, $offset_y=0){

    $base_image = str_replace(' ', config('constants.SPACE_ENCODING'), $base_image);
    $top_image = str_replace(' ', config('constants.SPACE_ENCODING'), $top_image);
    $hash_name = sha1(implode('_', func_get_args()));
    $name = $hash_name.".png";
    $s3 = \Storage::disk('s3');
    $s3_bucket = env("S3_BUCKET_NAME");
    $filePath = '/company_logo/' . $name;
    $full_url = config('constants.s3.url').$s3_bucket."".$filePath;
    if(!is_exists($full_url)){
      $base_image = Image::make($base_image);
      $top_image = Image::make($top_image);
      $base_image->insert($top_image, $position, $offset_x, $offset_y)->encode('data-url');
      $s3->put($filePath, file_get_contents($base_image), 'public');
    }
    return $full_url;
}

function addTextToImage($base_image, $text='Hello!', $alignment='center', $offset_x=0, $offset_y=0){
    $hash_name = sha1(implode('_', func_get_args()));
    $name = $hash_name.".png";
    $s3 = \Storage::disk('s3');
    $s3_bucket = env("S3_BUCKET_NAME");
    $filePath = '/company_logo/' . $name;
    $full_url = config('constants.s3.url').$s3_bucket."".$filePath;
    if(!is_exists($full_url)){
      Image::configure(array('driver' => 'imagick'));
      $base_image = Image::make($base_image);
      $base_image->text($text, $offset_x, $offset_y, function($font) {
          $font->file(public_path('fonts/mada-semibold.ttf'));
          $font->size(21);
          $font->color('#ffffff');
          $font->align('left');
          $font->valign('middle');
        })->encode('data-url');

      $s3->put($filePath, file_get_contents($base_image), 'public');
    }
    return $full_url;
}

function filePathJsonToUrl($file_path){
  return composeUrl($file_path);
}

function filePathUrlToJson($file_url, $json_response=true){
  if(empty($file_url)){
    return false;
  }
  $file_url = urldecode($file_url);
  $file_path = [];
  $s3_bucket = env("S3_BUCKET_NAME");
  $s3_bucket = $s3_bucket.'/';
  $temp_url = strpos($file_url, $s3_bucket) ? explode($s3_bucket, $file_url)[1] : $file_url;
  $file_path_array = explode('/', $temp_url);
  if(sizeOfCustom($file_path_array) > 0){
    $file_path['path'] = array_slice($file_path_array, 0, -1);
    $file_path['file'] = end($file_path_array);
  }
  return $json_response ? json_encode($file_path) : $file_path;
}
function getCircleImage($logo, $name, $crop_fit=false, $file_path=''){
            if(!sizeOfCustom($logo)){
               return '';
            }
            ini_set('max_execution_time', -1);
            $logo = str_replace(" ","+",$logo);
            $image = Image::make($logo);
            $image->encode('png');
            if($crop_fit){
                $image->fit(200);
            }else{
                $image->resize(125,125);
            }
            // create empty canvas
            $width = $image->getWidth();
            $height = $image->getHeight();        
            $canvas_size = $width<=$height?$width:$height;
            $mask = \Image::canvas($canvas_size, $canvas_size);
            
            // draw a white circle
            $mask->circle($canvas_size, $canvas_size/2, $canvas_size/2, function ($draw) {
                $draw->background('#fff');
                $draw->border(1,'#FA0505');
            });
            $data = $image->mask(getAwsSignedURL(composeUrl("/1488362727.png", false)), true)->encode('data-url');
            $file_path = ($file_path) ? $file_path:'company_logo';
            $name = ($name) ? $name : rand()."_".time().".png";
            return uploadImageToAws($data, $file_path, $name);
    }
function uploadImageToAws($data, $file_path, $name) {
        $s3 = \Storage::disk('s3');
        $s3_bucket = getenv("S3_BUCKET_NAME");
        $filePath = '/' . $file_path . '/' . $name;
        $full_url = config('constants.s3.url') . $s3_bucket . "" . $filePath;
        $s3->put($filePath, file_get_contents($data), 'public');
        return [
            'path' => [$file_path],
            'file' => $name
        ];
}
function createCircluarInitialsImages() {
    $background =  config('constants.s3.url') . getenv("S3_BUCKET_NAME") . "/".config('constants.NAME_INITIALS_BACKGROUND_IMAGE');
    ini_set('max_execution_time', -1);
    $first_name = $last_name = range('A', 'Z');
        foreach ($first_name as $first_initial) {
            foreach ($last_name as $second_initial) {
                $name = $first_initial.$second_initial;
                $image = Image::make($background);
                $image->encode('png');
                $image->text($name, 100, 100, function($font) {
                    $font->file(public_path('/fonts/Open_Sans/OpenSans-SemiBold.ttf'));
                    $font->size(74);
                    $font->color('#fff');
                    $font->align('center');
                    $font->valign('center');
                });
                $image->save(public_path('/images/name_initials/'.strtolower($name).'.png'));
            }
        }
    return "Name Initials created successfully ";
  }

function getVersion($call_back,$space_id=""){
  
  if($space_id == ""){

    $active_space = \Auth::user()->active_space;
    if(!$active_space)
      return $call_back['v1'];
    
    $active_space = json_decode($active_space,true);
    if(!isset($active_space['last_space']))
      return $call_back['v1'];
    
    $space_id = $active_space['last_space'];
  }
  $space = Space::find($space_id);
  if(!isset($space->version))
    return $call_back['v1'];

  if($space->version){
    return $call_back['v2'];
  }

  return $call_back['v1'];
}

function getCategorySlug($string){
  return str_replace(' ','_',preg_replace('!\s+!', ' ', str_replace('&','',$string)));
}
 
function arraysAreEqual($array1, $array2) {
    array_multisort($array1);
    array_multisort($array2);
    return ( serialize($array1) === serialize($array2) );
}

function wrapLinkedinJson($linkedin_data){
    $linkedin_data = json_decode(json_encode($linkedin_data), true);
    return [
        "token" => $linkedin_data['token'],
        "refreshToken" => $linkedin_data['refreshToken'],
        "expiresIn" => $linkedin_data['expiresIn'],
        "id" => $linkedin_data['id'],
        "nickname" => $linkedin_data['nickname'],
        "name" => $linkedin_data['name'],
        "email" => $linkedin_data['email'],
        "avatar" => $linkedin_data['avatar'],
        "user" => [
            "emailAddress" => $linkedin_data['email'],
            "firstName" => $linkedin_data['first_name'],
            "formattedName" => $linkedin_data['name'],
            "headline" => '',
            "id" => $linkedin_data['id'],
            "industry" => '',
            "lastName" => $linkedin_data['last_name'],
            "location" => [
                "country" => [
                    "code" => ''
                ],
                "name" => ''
            ],
            "pictureUrl" => $linkedin_data['avatar'],
            "pictureUrls" => [
                "_total" => 1,
                "values" => [$linkedin_data['avatar']]
            ],
            "positions" => [
                "_total" => 0,
                "values" => []
            ],
            "publicProfileUrl" => ''
        ],
        "avatar_original" => $linkedin_data['avatar_original']   
    ];
}

function getTwitterHandlersArray($twitter) {
    return $twitter ? array_filter(json_decode($twitter_handles ?? $twitter, true)) : [];
}

function csvToArraySoftLaunch()
{
    $csv = env('SOFT_LAUNCH_CSV');
    if($csv){
        $urls =  array_map('str_getcsv', file($csv));
        return getSpaceIdFromUrl($urls);
    }
    return false;
}

function getSpaceIdFromUrl($urls){
    $output = [];
    foreach($urls as $url){
        if($url[0]){
            $split_url = explode('/',$url[0]);
            if(isset($split_url[4])){
                if(checkUuidFormat($split_url[4])){
                    $output[] = $split_url[4];
                }
            }
        }
    }
    return $output;
}

function limitAlertStringWithRedirect($text, $maxchar, $redirect_link, $end = '...') {
  $output = $text;
  if (strlen($text) > $maxchar || $text == '') {
    $words = preg_split('/\s/', $text);
    $output = '';
    $counter = 0;
    foreach ($words as $key => $value) {
      $is_link = false;
      if(checkLink($words[$counter])){
        $is_link = $words[$counter];
        $words[$counter] = ' link';
      }

      $length = strlen($output)+strlen($words[$counter]);
      if ($length > $maxchar) {
        $output .= " " . ($is_link?linkToTest($is_link, $is_link):$words[$counter]);
        break;
      }
      $output .= " " . ($is_link?linkToTest($is_link, $is_link):$words[$counter]);
      ++$counter;
    }
    $output .= $end;
    } else {
      return linkToTest($output, $redirect_link);
    }
}

function isBadPath($path)
{
    return stripos($path, '../') !== false || stripos($path, '..\\') !== false;
}