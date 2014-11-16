<?php

namespace App\AppModule\Presenters;

use App\Components\Example\Form\FormControl;
use App\Components\Example\Form\IFormControlFactory;

/**
 * Examples presenter
 */
class ExamplesPresenter extends BasePresenter
{

	/** @var IFormControlFactory @inject */
	public $iFormControlFactory;
	
	/**
	 * @secured
	 * @resource('examples')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		$this->redirect('form');
	}

	/**
	 * @secured
	 * @resource('examples')
	 * @privilege('form')
	 */
	public function actionForm()
	{
		
	}

	// <editor-fold defaultstate="collapsed" desc="components">

	/** @return FormControl */
	protected function createComponentForm()
	{
		return $this->iFormControlFactory->create();
	}
	// </editor-fold>

}
