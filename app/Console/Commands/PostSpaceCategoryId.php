<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Post\PostInterface;


class PostSpaceCategoryId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:PostSpaceCategoryId';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command assign random Categories to old posts';

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
        return $this->post->SetRandomCategoryId();
    }
}
