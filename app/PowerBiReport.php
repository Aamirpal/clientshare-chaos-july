<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PowerBiReport extends Model
{
    protected $fillable = ['space_id', 'user_id', 'report_type', 'metadata', 'report_name'];
    protected $casts = [
    	'metadata'=> 'json'
    ];

    public function removeReport($space_id, $report_id){
    	return $this->where(['space_id'=>$space_id, 'id' => $report_id])->delete();
    }

    public function shareReportList($space_id) {
    	return $this->where('space_id', $space_id)->get()->toArray();
    }
}
