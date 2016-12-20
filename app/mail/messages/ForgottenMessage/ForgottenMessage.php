<?php

namespace App\Mail\Messages;

class ForgottenMessage extends BaseMessage
{

	protected function beforeSend()
	{
		$this->setFrom($this->settings->mails->automatFrom, $this->settings->pageInfo->projectName);
		$this->setSubject($this->translator->translate('Lost password'));
		parent::beforeSend();
	}

}

interface IForgottenMessageFactory
{

	/**
	 * @return ForgottenMessage
	 */
	public function create();
}
