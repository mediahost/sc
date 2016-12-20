<?php

namespace App\Mail\Messages;

class VerificationMessage extends BaseMessage
{

	protected function beforeSend()
	{
		$this->setFrom($this->settings->mails->automatFrom, $this->settings->pageInfo->projectName);
		$this->setSubject($this->translator->translate('Verify your e-mail'));
		parent::beforeSend();
	}

}

interface IVerificationMessageFactory
{

	/**
	 * @return VerificationMessage
	 */
	public function create();
}
