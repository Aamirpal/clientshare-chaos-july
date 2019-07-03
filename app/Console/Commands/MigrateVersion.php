<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Space\SpaceInterface;


class MigrateVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:MigrateVersion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It will Migrate the version of CS from V1 to v2';

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
        $soft_launch = csvToArraySoftLaunch();
        $version = $this->choice('Which Version You want to move?', [1=>'V1', 2=>'V2'], 1);
        $this->space->migrateVersion($version, $soft_launch);
        $this->info($soft_launch ? "SOFT launch shares are updated to version $version." : "All shares are updated to version $version.");
    }
}
