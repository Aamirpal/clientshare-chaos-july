<?php

namespace App\Repositories\PostMedia;

interface PostMediaInterface
{
	public function getAttachments($post_id);
	public function getAttachmentsById($attachments_id);
	public function getAttachmentByUrl($url);
	public function deleteAttachments($attachments_id);
	public function postFiles($request, $login_user);
}
