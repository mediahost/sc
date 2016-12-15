<?php

namespace App\Mail\Messages;


class ShareMessage extends BaseMessage
{
	
	public function __construct()
	{
		parent::__construct();
		$this->setFrom('system@source-code.com');
		$this->setSubject('Curriculum Vitae');
	}
}


interface IShareMessageFactory
{

	/**
	 * @return ShareMessage
	 */
	public function create();
}