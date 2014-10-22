<?php

namespace App\FrontModule\Presenters;

use Nette;
use Tracy\Debugger as Debug;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	// <editor-fold defaultstate="collapsed" desc="constants & variables">
	// </editor-fold>

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
