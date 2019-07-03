<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Post\PostInterface;


class MapCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:MapCategories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It will map categories of v1 with v2';

    protected $post;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PostInterface $post)
    {
        parent::__construct();
        $this->post = $post;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->post->mergeCategories();
        $this->info('All categories are mapped successfully.');
    }
}
