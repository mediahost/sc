<?php

namespace App\Mail\Messages;

class CreateRegistrationMessage extends BaseMessage
{

	public function __construct()
	{
		parent::__construct();
		$this->setFrom($this->settings->mails->automatFrom, $this->settings->pageInfo->projectName);
		$this->setSubject($this->translator->translate('Your registration'));
	}

}

interface ICreateRegistrationMessageFactory
{

	/**
	 * @return CreateRegistrationMessage
	 */
	public function create();
}
