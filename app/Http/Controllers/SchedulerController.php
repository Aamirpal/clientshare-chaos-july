<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Traits\Scheduler;

class SchedulerController extends Controller
{
	use Scheduler;

	public function weeklySummary() {
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);
        if (date('w') == env('weekly_email',5)) {
        	$this->weeklySummaryTrigger();
        }
    }
}
