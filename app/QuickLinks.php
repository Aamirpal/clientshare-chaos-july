<?php

namespace App;
use Image;
use Carbon\Carbon;
use App\Helpers\Aws;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuickLinks extends Model {

    protected $fillable = [
        'share_id', 'user_id', 'link_url', 'link_name'
    ];

    public static function saveLinks($form_data) {
        if(sizeOfCustom($form_data)>0){
            static::where('share_id',$form_data['space_id'])->delete();
            for($i=0;$i<4;$i++){
                    if(!empty($form_data['hyperlink'][$i])){
                         static::create([
                            'share_id' => $form_data['space_id'],
                            'user_id' => $form_data['user_id'],
                            'link_url' => $form_data['hyperlink'][$i],
                            'link_name' => $form_data['link_name'][$i]
                          ]);
                    }
            }
        }
    }

    public static function getQuickLinks($share_id) {
         return static::where('share_id',$share_id)->get()->toArray();
    }
    
}