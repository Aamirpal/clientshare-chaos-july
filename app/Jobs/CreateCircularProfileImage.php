<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\User;
class CreateCircularProfileImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    Protected $user_id;
    public function __construct($user_id)
    {
         $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        try{
        $user = User::find($this->user_id);
        $user_profile_image = $user->profile_image;
        if (!empty($user_profile_image)) {
            $image_path = composeUrl($user_profile_image, true);
            if ($image_path && strpos($image_path, config('constants.LINKED_IN_URL')) !== false)
                return;
            $path = 'user_profile_thumbnail';
            $file_name = 'circular_' . $this->user_id . '.png';
            $circular_url = getCircleImage(composeUrl($user_profile_image, true), $file_name, true, $path);
            $user_data = User::updateUser($user['id'], ['circular_profile_image' => json_encode($circular_url)]);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}
