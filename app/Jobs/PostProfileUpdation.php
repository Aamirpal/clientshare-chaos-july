<?php

namespace App\Jobs;

use App\SpaceUser;
use App\Helpers\ApplicationAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PostProfileUpdation implements ShouldQueue {
    use InteractsWithQueue, Queueable, SerializesModels;

    public function handle(){
        if(SpaceUser::incompleteProfileUsers('count')){
            (new ApplicationAlert)->trigger();
        }
    }
}
