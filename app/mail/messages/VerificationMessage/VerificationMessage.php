<?php

namespace App\Mail\Messages;

class VerificationMessage extends BaseMessage
{

	public function __construct()
	{
		parent::__construct();
		$this->setFrom('system@source-code.com');
		$this->setSubject('Verify your e-mail');
	}

}

interface IVerificationMessageFactory
{

	/**
	 * @return VerificationMessage
	 */
	public function create();
}
