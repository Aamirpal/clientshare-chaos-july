<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Space\SpaceInterface;
use App\Repositories\SpaceCategory\SpaceCategoryInterface;


class SetDefaultCategory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:SetDefaultCategory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command create default Categories for old spaces';

    protected $space_category;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SpaceCategoryInterface $space_category, SpaceInterface $space)
    {
        parent::__construct();
        $this->space_category = $space_category;
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
            $this->space_category->setDefaultCategory($space_id);
            $bar->advance();
        } 
        $bar->finish();
        $this->info($soft_launch ? 'All categories for SOFT launch has been created successfully.' : 'All categories has been created successfully.');
    }
}
