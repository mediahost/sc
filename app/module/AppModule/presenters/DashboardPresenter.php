<?php

namespace App\AppModule\Presenters;
use App\Mail\Messages\ICreateRegistrationMessageFactory;

class DashboardPresenter extends BasePresenter
{

	/** @var ICreateRegistrationMessageFactory @inject */
//	public $message;

	/**
	 * @secured
	 * @resource('dashboard')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
//		$message = $this->message->create();
//		$message->addTo($this->user->identity->mail);
//		$message->send();
	}

}
