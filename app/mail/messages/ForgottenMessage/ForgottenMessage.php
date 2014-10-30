<?php

namespace App\Mail\Messages;

class ForgottenMessage extends BaseMessage
{

	public function __construct()
	{
		$this->setFrom('noreply@sc.com');
		$this->setSubject('Lost password');
	}

}
