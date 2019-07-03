<?php

namespace App\Jobs;

use App\Space;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateJointShareLogos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;   
   
    Protected $space_id;
    public function __construct($id)
    {
         $this->space_id = $id;
    }

    public function handle()
    {
        return (new Space)->generateEmailBannerLogosforShares($this->space_id);
    }
}
