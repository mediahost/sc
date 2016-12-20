<?php

namespace App\Mail\Messages;


class ShareMessage extends BaseMessage
{

	protected function beforeSend()
	{
		$this->setFrom($this->settings->mails->automatFrom, $this->settings->pageInfo->projectName);
		$this->setSubject($this->translator->translate('Curriculum Vitae'));
		parent::beforeSend();
	}
}


interface IShareMessageFactory
{

	/**
	 * @return ShareMessage
	 */
	public function create();
}