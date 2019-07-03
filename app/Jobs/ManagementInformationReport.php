<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;

use \App\Traits\ManagementInformation as MITrait;

class ManagementInformationReport implements ShouldQueue{
    
    use InteractsWithQueue, Queueable, SerializesModels, MITrait;
    
    protected $request;
    public function __construct($request){
        $this->request = $request->all();
    }

    public function handle(){
        $request = new Request();
        $request->merge($this->request);
        return $this->loadExcelData($request);
    }
}
