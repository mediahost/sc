<?php

namespace App\Mail\Messages;

class ForgottenMessage extends BaseMessage
{

	public function __construct()
	{
		parent::__construct();
		$this->setFrom('system@source-code.com');
		$this->setSubject('Lost password');
	}

}

interface IForgottenMessageFactory
{

	/**
	 * @return ForgottenMessage
	 */
	public function create();
}
