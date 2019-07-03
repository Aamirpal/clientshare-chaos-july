<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Group\GroupInterface;
use App\Repositories\Space\SpaceInterface;


class LaunchVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:LaunchVersion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to launch version';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $default_group = $this->choice('Do you want to create Default groups?', [1=>'yes', 2=>'no'], 1);
        if($default_group == 'yes'){
            $this->call('command:createDefaultGroup');
        }

        $post_visibility_group = $this->choice('Do you want to create groups as per post visibility?', [1=>'yes', 2=>'no'], 1);
        if($post_visibility_group == 'yes'){
            $this->call('command:MakeGroup');
        }

        $default_categories = $this->choice('Do you want to set defalut categories?', [1=>'yes', 2=>'no'], 1);
        if($default_categories == 'yes'){
            $this->call('command:SetDefaultCategory');
        }

        $map_categories = $this->choice('Do you want to map categories?', [1=>'yes', 2=>'no'], 1);
        if($map_categories == 'yes'){
            $this->call('command:MapCategories');
        }

        $version_update = $this->choice('Do you want to change plateform version?', [1=>'yes', 2=>'no'], 1);
        if($version_update == 'yes'){
            $this->call('command:MigrateVersion');
        }
    }
}
