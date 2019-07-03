<?php

namespace App\Helpers;

use App\SpaceUser;
use App\Helpers\Logger;
use App\Traits\Mailer;

class ApplicationAlert {
	use Mailer;
	const APPLICATION_ALERT_EMAILS = ['aamirpal@ucreate.co.in', 'joe@ucreate.it'];
	protected $alert_data;
	public function trigger(){
		$this->checkIncompleteProfileUsers();
		$this->log();
		$this->sendEmailAlert();
	}
	
	private function log(){
		return (new Logger)->log([
            'action' => 'log application alert',
            'metadata' =>  $this->alert_data
        ]);
	}

	private function sendEmailAlert(){
		$any_issue = 0;
		foreach ($this->alert_data as $key => $value) {
			$mail_data[$key] = sizeOfCustom($value);
			$any_issue += sizeOfCustom($value);
		}
		return $any_issue ? $this->applicationAlert(static::APPLICATION_ALERT_EMAILS, $mail_data??[]) : null;
	}

	private function checkIncompleteProfileUsers(){
		return $this->alert_data['incomplete_profile_users'] = SpaceUser::incompleteProfileUsers();
	}

}