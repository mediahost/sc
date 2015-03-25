<?php

namespace App\FrontModule\Presenters;

class HomepagePresenter extends BasePresenter
{
	
	protected function beforeRender()
	{
		$this->setDemoLayout();
		parent::beforeRender();
	}

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
