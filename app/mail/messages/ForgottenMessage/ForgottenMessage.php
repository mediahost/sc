<?php

namespace App\Mail\Messages;

class ForgottenMessage extends BaseMessage
{

	public function __construct()
	{
		parent::__construct();
		$this->setFrom($this->settings->mails->automatFrom, $this->settings->pageInfo->projectName);
		$this->setSubject($this->translator->translate('Lost password'));
	}

}

interface IForgottenMessageFactory
{

	/**
	 * @return ForgottenMessage
	 */
	public function create();
}
