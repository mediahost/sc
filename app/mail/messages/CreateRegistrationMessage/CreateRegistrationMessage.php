<?php

namespace App\Mail\Messages;

class CreateRegistrationMessage extends BaseMessage
{

	protected function beforeSend()
	{
		$this->setFrom($this->settings->mails->automatFrom, $this->settings->pageInfo->projectName);
		$this->setSubject($this->translator->translate('Your registration'));
		parent::beforeSend();
	}

}

interface ICreateRegistrationMessageFactory
{

	/**
	 * @return CreateRegistrationMessage
	 */
	public function create();
}
