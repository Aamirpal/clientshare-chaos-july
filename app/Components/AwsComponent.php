<?php

namespace App\Components;

use Illuminate\Support\Facades\Input;

class AwsComponent {

    public function uploadImage($file_object) {
        if (!empty($file_object['tmp_name'])) {
            $mime = mime_content_type($file_object['tmp_name']);
            $mime = (array_filter(explode('/', $mime)));
            if (in_array(array_pop($mime), config('constants.IMAGE_EXTENSIONS'))) {
                $image = imagecreatefromstring(file_get_contents($file_object['tmp_name']));
                $exif = @exif_read_data($file_object['tmp_name']);
                if (!empty($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 8:
                            $image = imagerotate($image, 90, 0);
                            break;
                        case 3:
                            $image = imagerotate($image, 180, 0);
                            break;
                        case 6:
                            $image = imagerotate($image, -90, 0);
                            break;
                    }
                }
              
                $file = Input::file('file');
                $extension = $file->guessExtension();
                $name = time() . '.' . $extension; 
        
                $create_image = \Image::make($image);
                $create_image->encode($extension);
                return $this->uploadImageToS3($create_image,'',$name);
            }
        }
    }
  
    private function uploadImageToS3($data, $file_path = '', $name = null) {
        $s3 = \Storage::disk('s3');
        $s3_bucket = getenv("S3_BUCKET_NAME");
        $file_path = $file_path . '/' . $name;
        $full_url = config('constants.s3.url') . $s3_bucket . $file_path;
        $s3->put($file_path, (string) $data, 'public');
        return filePathUrlToJson($full_url);
    }

}
