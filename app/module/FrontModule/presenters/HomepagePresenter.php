<?php

namespace App\FrontModule\Presenters;

class HomepagePresenter extends BasePresenter
{

	public function actionDefault()
	{
		if ($this->user->loggedIn) {
			$this->redirect(':App:Dashboard:');
		} else {
			$this->redirectUrl('/wp/');
		}
	}

}
