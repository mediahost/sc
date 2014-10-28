<?php

namespace App\FrontModule\Presenters;

use App\Components\Installer;

/**
 * Install presenter.
 */
class InstallPresenter extends BasePresenter
{
	// <editor-fold defaultstate="collapsed" desc="constants & variables">

	/** @var array */
	private $messages = [];

	/** @var Installer @inject */
	public $installer;

	// </editor-fold>

	public function actionDefault()
	{
		$this->messages = $this->installer->install();
	}
	
	public function renderDefault($printHtml = TRUE)
	{
		$this->template->html = (bool) $printHtml;
		$this->template->messages = $this->messages;
	}

}
