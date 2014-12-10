<?php

namespace App\FrontModule\Presenters;

abstract class BasePresenter extends \App\BaseModule\Presenters\BasePresenter
{

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->pacePluginOff = TRUE;
	}

}
