<?php

namespace App\Mail\Messages;

class VerificationMessage extends BaseMessage
{

	public function __construct()
	{
		parent::__construct();
		$this->setFrom($this->settings->mails->automatFrom, $this->settings->pageInfo->projectName);
		$this->setSubject($this->translator->translate('Verify your e-mail'));
	}

}

interface IVerificationMessageFactory
{

	/**
	 * @return VerificationMessage
	 */
	public function create();
}
