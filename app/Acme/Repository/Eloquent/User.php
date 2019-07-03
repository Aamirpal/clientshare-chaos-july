<?php

namespace Acme\Repository\Eloquent;

use Acme\Repository\UserInterface;

use App\User as UserModel;

class User implements UserInterface
{
	protected $user;

	public function __construct(UserModel $user)
	{
		$this->user = $user;
	}

	public function updateUser($user_id, $data)
	{
		return $this->user->where('id', $user_id)
			->update($data);
	}

	public function generateProfileThumbnail()
	{
        $users = $this->user->select('id', 'profile_thumbnail', 'profile_image')
            ->whereNull('profile_thumbnail')
            ->whereNotNull('profile_image')
            ->get()->toArray();

        foreach ($users as $key => $user) {
            $image_path = composeUrl($user['profile_image'], true);
            if(trim($image_path) == '' || empty($image_path))
                continue;
            if($image_path && strpos($image_path, config('constants.LINKED_IN_URL')) !== false)
                continue;

            $user_profile_image = $user['profile_image'];
            if($image_path && strpos($image_path, config('constants.LINKED_IN_URL')) !== false) {
                $data['profile_image'] = filePathUrlToJson($image_path);
                $this->user->where('id', $user['id'])
                           ->update($data);
                $update_user_data = $this->user->where('id', $user['id'])
                               ->first();
                $user_profile_image = $update_user_data->profile_image;
            }
            $dimensions = generateImageThumbnail(composeUrl($user_profile_image, true), 125, 125, true, true);
            
            if(!sizeOfCustom($dimensions)) 
                continue;

            $path = '/user_profile_thumbnail/';
            $file_name = rand().time().'.png';
            resizeImage(
                composeUrl($user_profile_image),
                $dimensions['thumbnail_image_width'],
                $dimensions['thumbnail_image_height'],
                false,
                $path.$file_name
            );

            $profile_thumbnail = [
                'path' => array_values(array_filter(explode('/', $path))),
                'file' => $file_name
            ];

            $this->user->where('id', $user['id'])
            	->update([
            		'profile_thumbnail' => json_encode($profile_thumbnail)
            	]);
        }
    }
	public function generateCircularProfileThumbnail()
	{
        $users = $this->user->select('id', 'circular_profile_image', 'profile_image')
            ->whereNull('circular_profile_image')
            ->whereNotNull('profile_image')
            ->get()->toArray();
 
        foreach ($users as $key => $user) {
            $image_path = composeUrl($user['profile_image'], true);
            if(trim($image_path) == '' || empty($image_path))
                continue;
            if($image_path && strpos($image_path, config('constants.LINKED_IN_URL')) !== false)
                continue;

            $path = 'user_profile_thumbnail';
            $file_name = 'circular_'.rand().'_'.time().'.png';
            $circular_url= getCircleImage(composeUrl($user['profile_image'], true),$file_name,true,$path);
            if ($circular_url != '') {
                $this->user->where('id', $user['id'])
                    ->update([
                        'circular_profile_image' => json_encode($circular_url)
                ]);
            }
        }
    }
}