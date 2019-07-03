<?php

namespace App\Helpers;
use DB;
use Illuminate\Http\Request;

class Mailgun {

	/**/
	public function log_drop( Request $request ) {
		$parsed_data = $this->parse_drop_mail( $request->all() );
		if(!$parsed_data) return 0;

		(new \App\Http\Controllers\MailerController)->mailgunDrop($parsed_data);

		return (new Logger)->log([
	        'user_id'     => "00000000-0000-0000-0000-000000000000",
	        'content_type'=> 'Mailgun',
	        'action'      => $parsed_data['event'],
	        'description' => $parsed_data['description']??$parsed_data['event'],
	        'content_id'  => $parsed_data['Message-Id'],
	        'metadata'    => json_encode($parsed_data)
	    ]);

	}

	/**/
	public function parse_drop_mail( $obj ) {
		if(!isset($obj['message-headers'])) return 0;
		$message_headers = json_decode($obj['message-headers'], true);
		foreach ($message_headers as $key => $value) {
			$obj['parsed_mail_data'][$value[0]] = $value[1];
		}
		$obj['parsed_mail_data']['Subject'] = str_replace("- get", "", $obj['parsed_mail_data']['Subject']);
		return $obj;
	}

}