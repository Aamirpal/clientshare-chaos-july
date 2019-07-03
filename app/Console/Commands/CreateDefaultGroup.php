<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Group\GroupInterface;
use App\Repositories\Space\SpaceInterface;


class CreateDefaultGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:createDefaultGroup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command create Everyone group';

    protected $group;
    protected $space;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(GroupInterface $group, SpaceInterface $space)
    {
        parent::__construct();
        $this->group = $group;
        $this->space = $space;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {   
        $soft_launch = csvToArraySoftLaunch();
        $space_ids = $soft_launch ? $soft_launch : $this->space->getAllSpaces()->pluck('id');
        $bar = $this->output->createProgressBar(count($space_ids));
        $bar->start();
        foreach($space_ids as $space_id){
            $this->group->createDefaultGroup($space_id);
            $bar->advance();
        }
        $bar->finish();
        $this->info($soft_launch ? 'Defalut group for SOFT launch shares has been created successfully.' : 'Defalut group for all shares has been created successfully.');
    }
}
