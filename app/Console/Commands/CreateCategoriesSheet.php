<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Space\SpaceInterface;

class CreateCategoriesSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:categoriesSheet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command create a sheet of all existing categories of V1.';

    protected $space;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SpaceInterface $space)
    {
        parent::__construct();
        $this->space = $space;
    }

    /**
     * Execute the console command. 
     *
     * @return mixed
     */
    public function handle()
    {
        $sheet = $this->space->shareCategorySheet();
        $this->info('Sheet has been created successfully. Use this link to download sheet:- '. $sheet);
    }
}
