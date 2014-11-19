<?php

namespace App\Mail\Messages;

class CreateRegistrationMessage extends BaseMessage
{

	public function __construct()
	{
		$this->setFrom('noreply@sc.com');
		$this->setSubject('Your registration');
	}

}
