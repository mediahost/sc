<?php

namespace App\Mail\Messages;

class VerificationMessage extends BaseMessage
{

	public function __construct()
	{
		$this->setFrom('noreply@sc.com');
		$this->setSubject('Verify your e-mail');
	}

}
