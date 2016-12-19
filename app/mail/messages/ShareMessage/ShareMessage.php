<?php

namespace App\Mail\Messages;


class ShareMessage extends BaseMessage
{
	
	public function __construct()
	{
		parent::__construct();
		$this->setFrom($this->settings->mails->automatFrom, $this->settings->pageInfo->projectName);
		$this->setSubject($this->translator->translate('Curriculum Vitae'));
	}
}


interface IShareMessageFactory
{

	/**
	 * @return ShareMessage
	 */
	public function create();
}