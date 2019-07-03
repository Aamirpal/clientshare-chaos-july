<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Acme\Repository\UserInterface;


class GenerateProfileThumbnail extends Command
{
    protected $signature = 'command:generateProfileThumbnail';

    protected $description = 'Generate profile image thumbnail.';

    protected $user;

    public function __construct(UserInterface $user)
    {
        parent::__construct();
        $this->user = $user;
    }

    public function handle()
    {
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);
        $this->user->generateCircularProfileThumbnail();
        return $this->user->generateProfileThumbnail();
    }
}
