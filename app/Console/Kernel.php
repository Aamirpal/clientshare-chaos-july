<?php

namespace App\Console;

use App\SpaceUser;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ManageShareController;
use App\Http\Controllers\SchedulerController;
use App\Http\Controllers\FeedbackController;
use App\Helpers\ApplicationAlert;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
    
    protected $commands = [
      Commands\GenerateProfileThumbnail::class,
      Commands\CreateSpacesBanner::class,
      Commands\SetDefaultCategory::class,
      Commands\PostSpaceCategoryId::class,
      Commands\MapCategories::class,
      Commands\MakeGroup::class,
      Commands\CreateDefaultGroup::class,
      Commands\CreateCategoriesSheet::class,
      Commands\MigrateVersion::class,
      Commands\LaunchVersion::class,
      Commands\RenameprocessLogo::class,
      Commands\UpdateUrlPreview::class
    ];
    
    protected function schedule(Schedule $schedule) {

        $schedule->call(function () { 
          (new PostController)->triggerFeedback();
        })->everyMinute(); 

        $schedule->call(function () {
          (new SchedulerController)->weeklySummary();
        })->everyMinute();
        
        $schedule->call(function () {
          (new ManageShareController)->pendingInvites();
        })->everyMinute();
       
        $schedule->call(function () {
          (new FeedbackController)->feedbackCloseNotification();
        })->everyMinute();

        $schedule->call(function () {
          (new FeedbackController)->feedbackAdminReminder();
        })->everyMinute();

        $schedule->call(function () {
          (new ApplicationAlert)->trigger();
        })->everyMinute();

        $schedule->command("command:generateProfileThumbnail")->everyMinute();
    }

}
