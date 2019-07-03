<?php

namespace Tests\Feature;

use Tests\ParentTestClass;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class FileViewerControllerTest extends ParentTestClass
{
    use WithoutMiddleware;

    public function testGetViewer()
    {
        $file_data = [
            'url'=>'https://uat-clientshare.s3.amazonaws.com/postfile/f613ecf8-0e57-4e2c-9e73-9edb8ec8fffb.pdf',
            'extension'=>'pdf'
        ];
        $response = $this->post('get-viewer', $file_data);
        $response->assertSeeText('code');
        $response->assertStatus(200);
    }
}
