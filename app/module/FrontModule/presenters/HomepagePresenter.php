<?php

namespace App\FrontModule\Presenters;

use Nette;
use Tracy\Debugger as Debug;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	/** @var \App\Model\Storage\SettingsStorage @inject */
	public $mySettings;

	public function actionDefault()
	{
		$usersSettings = $this->mySettings->getModuleSettings('users');
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
