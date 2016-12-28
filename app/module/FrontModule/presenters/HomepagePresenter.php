<?php

namespace App\FrontModule\Presenters;

class HomepagePresenter extends BasePresenter
{

	public function actionDefault()
	{
		if ($this->user->loggedIn) {
			$this->redirect(':App:Dashboard:');
		} else if ($this->getHttpRequest()->getRemoteAddress() == '127.0.0.1') {
			$this->redirect('Sign:in');
		} else {
			$this->redirectUrl('/wp/');
		}
	}

}
