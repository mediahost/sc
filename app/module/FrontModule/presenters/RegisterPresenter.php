<?php

namespace App\FrontModule\Presenters;

class RegisterPresenter extends BasePresenter
{
	public function actionCompany()
	{
		$this->forward('Sign:registration');
	}
	
	public function actionCandidate()
	{
		$this->forward('Sign:registration');
	}
}
