<?php

namespace App\Mail\Messages;

class CreateRegistrationMessage extends BaseMessage
{

	public function __construct()
	{
		parent::__construct();
		$this->setFrom('system@source-code.com');
		$this->setSubject('Your registration');
	}

}

interface ICreateRegistrationMessageFactory
{

	/**
	 * @return CreateRegistrationMessage
	 */
	public function create();
}
