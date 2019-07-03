<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Space;


class CreateSpacesBanner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:createSpacesBanner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command create Spaces Banner';

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
        return (new Space)->generateEmailBannerLogosforShares();
    }
}
