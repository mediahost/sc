<?php

namespace App\FrontModule\Presenters;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	public function actionDefault()
	{
		
	}

	public function renderTest1()
	{
		$this->template->backlink = $this->storeRequest();
	}

	public function renderTest2()
	{
		$this->template->backlink = $this->storeRequest();
	}

}
