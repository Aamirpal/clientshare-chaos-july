<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Controllers\ManageShareController;
use App\Http\Controllers\MailerController;
use Config;
use App\Space;
use Storage;

class EmailHeaderImage implements ShouldQueue {
    use InteractsWithQueue, Queueable;

    Protected $space_id;

    public function __construct($space_id) {
        $this->space_id = $space_id;
    }

    public function handle() {
      $space_data = Space::spaceById($this->space_id,'first');
      if(!empty($space_data)){
        $background_image = wrapUrl($space_data->background_image);
        $s3 = \Storage::disk('s3');

        $url = urldecode($background_image);
        $url_array = explode("/", $url);
        $reversed_url_array = array_reverse($url_array);
        foreach ($url_array as $key => $url_index) {
          if( $url_index != env("S3_BUCKET_NAME")) {
            array_pop($reversed_url_array);
          } else {
            array_pop($reversed_url_array);
            break;
          }
        }
        $reversed_url_array = array_reverse($reversed_url_array);
        $implode_url = implode('/', $reversed_url_array);
        $explode_url = explode("&", $implode_url);
        
        if(!$s3->exists($explode_url[0]) || 
            strpos(composeUrl($space_data->processed_buyer_logo),env('APP_URL').'/images') !== false || 
            strpos(composeUrl($space_data->processed_seller_logo),env('APP_URL').'/images') !== false)
            return false;
        if($background_image){
             $share_name = $space_data->share_name.' Clientshare';
             $seller_logo = wrapUrl($space_data->processed_seller_logo);
             $buyer_logo = wrapUrl($space_data->processed_buyer_logo);
             $bg_resized = resizeImage($background_image, 680, 104, true);
             $seller_logo_resized = resizeImage($seller_logo, 36, 36);
             $buyer_logo_resized = resizeImage($buyer_logo, 44, 44);
             $bg_seller_logo =  mergeImages($bg_resized, $seller_logo_resized, 'bottom-left', 66, 34);
             $bg_seller_buyer_logo =  mergeImages($bg_seller_logo, $buyer_logo_resized, 'bottom-left', 98, 30);
             $bg_seller_buyer_logo_text =  addTextToImage($bg_seller_buyer_logo, $share_name, 'left', 160, 50);
             $data['space']['email_header_image'] = $bg_seller_buyer_logo_text;
             $data['space']['email_header'] = filePathUrlToJson($bg_seller_buyer_logo_text);
             Space::updateSpaceById($space_data->id, $data['space']);
             return $bg_seller_buyer_logo_text;
        } 
        return false;
      }
      return false;
    }
}