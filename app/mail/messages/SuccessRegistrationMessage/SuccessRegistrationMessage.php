<?php

namespace App\Mail\Messages;

class SuccessRegistrationMessage extends BaseMessage
{

	public function __construct()
	{
		$this->setFrom('noreply@sc.com');
		$this->setSubject('Your registration');
	}

}
