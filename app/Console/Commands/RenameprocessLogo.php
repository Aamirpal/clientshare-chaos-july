<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Space\SpaceInterface;


class RenameprocessLogo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:renameProcessLogo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will rename the process logos.';

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
        $spaces = $this->space->getRenameProcessLogoShares();
        $bar = $this->output->createProgressBar(count($spaces));
        $bar->start();
        foreach($spaces as $space){
            $this->space->renameProcessLogo($space);
            $bar->advance();
        }
        $bar->finish();
        $this->info('All Logos has been renamed successfully.');
    }
}
