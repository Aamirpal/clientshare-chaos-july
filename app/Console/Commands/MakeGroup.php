<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Group\GroupInterface;
use App\Repositories\Post\PostInterface;

class MakeGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:makegroup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command create Groups for old posts as per their visibility.';

    protected $group;
    protected $post;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(GroupInterface $group, PostInterface $post)
    {
        parent::__construct();
        $this->group = $group;
        $this->post = $post;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $soft_launch = csvToArraySoftLaunch();
        $posts = $this->post->getPostsForGroupCommand($soft_launch ? $soft_launch : '');
        $bar = $this->output->createProgressBar(count($posts));
        $bar->start();
        foreach($posts as $post){
            $this->group->makeOldPostGroups($post);
            $bar->advance();
        }
        $bar->finish();
        $this->info($soft_launch ? 'All Groups for SOFT launch has been created successfully.' : 'All Groups has been created successfully.');
    }
}
