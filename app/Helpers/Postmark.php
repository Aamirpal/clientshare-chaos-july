<?php

namespace App\Helpers;
use App\Http\Controllers\MailerController;
use DB;
use Config;
use App\SpaceUser;
use Illuminate\Http\Request;

class Postmark {

	
	public function logDrop( Request $request ) {
		$parsed_data = $request->all();
		if(!sizeOfCustom($parsed_data)) return 0;

		(new Logger)->log([
	        'user_id'     => Config::get('constants.USER_ID_DEFAULT'),
	        'content_type'=> 'Postmark',
	        'action'      => $parsed_data['Name'],
	        'description' => $parsed_data['Description'],
	        'content_id'  => $parsed_data['MessageID'],
	        'metadata'    => json_encode($parsed_data)
	    ]);
		$get_postmark_message_details = $this->getPostmarkMessageDetails($parsed_data['MessageID']);

		if(empty($get_postmark_message_details)) return 0;

		$parsed_data['ReplyTo'] = $get_postmark_message_details['ReplyTo']??null;
		$parsed_data['space_id'] = $get_postmark_message_details['space_id']??null;
		$parsed_data['space_id'] = $parsed_data['space_id']??($get_postmark_message_details['share_id']??null);
		$get_postmark_message_details['Tag'] = $get_postmark_message_details['Tag']??null;

		(new Logger)->bouncedEmailLog([
	        'from_email' => $parsed_data['ReplyTo'],
	        'to_email' => $parsed_data['Email'],
	        'share_id' => $parsed_data['space_id'],
	        'metadata' => $parsed_data
	    ]);

	    if( in_array( $get_postmark_message_details['Tag'], Config::get('constants.email.blocked_tags')) ) return;
	    
		
		if( !in_array( $get_postmark_message_details['Tag'], Config::get('constants.email.blocked_tags_for_user')) )
			(new MailerController)->postmarkDrop($parsed_data);

	
		if($parsed_data['space_id'] && !(in_array( $get_postmark_message_details['Tag'], Config::get('constants.email.blocked_tags_for_admin')))) {
			$space_admins = SpaceUser::spaceUserExceptSender($parsed_data['space_id'], $parsed_data['ReplyTo'], 'admin', 'get');
			foreach ($space_admins as $space_admin_key => $space_admin_value) {
				$parsed_data['ReplyTo'] = $space_admin_value['user']['email'];
				(new MailerController)->postmarkDrop($parsed_data);
			}
		}
		return 1;
	}

	public function parseDropMailData( $mail_data ) {
		return json_decode($mail_data, true);
	}
	
	public function getPostmarkMessageDetails($email_id){
		$postmark_curl_data = $this->postmarkCurl($email_id);
		if(!is_array($postmark_curl_data) || sizeOfCustom($postmark_curl_data)) return 0;

		$postmark_curl_data['ReplyTo'] = $this->postmarkDataByKey($postmark_curl_data,'reply-to: ');
		$postmark_curl_data['space_id'] = $this->postmarkDataByKey($postmark_curl_data,'space_id: ');
		return $postmark_curl_data;
	}

	public function postmarkDataByKey($mail_data, $mail_string){
		if(!sizeOfCustom($mail_data) || !isset($mail_data['Body'])) return null;
		$string_start_index = strripos($mail_data['Body'], $mail_string);
		if($string_start_index === false) return null;
		for($string_index = strripos($mail_data['Body'], $mail_string); $string_index<=strlen($mail_data['Body']); $string_index++){
			if(ord ( $mail_data['Body'][$string_index] ) == 13){
				$string_end_index = $string_index;
				break;
			}
		}
		$data_extracted = trim(explode(":", substr($mail_data['Body'], $string_start_index, $string_end_index-$string_start_index))[1]??'');
		return $data_extracted;
	}

	public function postmarkCurl($email_id){
		$postmark_url = Config::get('constants.URL.postmark_curl').$email_id."/details";
		$headers = array(
			'Accept:application/json',
			'X-Postmark-Server-Token:'.env("MAIL_PASSWORD").''
		);
		$ch = curl_init();
	    $ch = curl_init($postmark_url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	    curl_setopt($ch, CURLOPT_TIMEOUT ,7);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    $content = curl_exec($ch);
	    curl_close($ch);
		if( !isset($content) || !$content ) return 0;

	    $data = json_decode($content, true);

		if(!sizeOfCustom($data) || !isset($data['Body'])) return 0;
		return $data;
	}
}
