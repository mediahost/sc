<?php

namespace App\Components;

use Nette\Application\UI,
	Nette\Localization\ITranslator;

/**
 * BaseControl.
 * @author Martin Šifra <me@martinsifra.cz>
 */
abstract class BaseControl extends UI\Control
{
	// <editor-fold defaultstate="collapsed" desc="constants & variables">

	/** @var ITranslator @inject */
	public $translator;

	// </editor-fold>

	public function __construct()
	{
		parent::__construct();
	}

	// <editor-fold defaultstate="collapsed" desc="setters">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">

	public function getTemplate()
	{
		$template = parent::getTemplate();
		$template->setTranslator($this->translator);
		return $template;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="renderers">

	public function render()
	{
		$dir = dirname($this->getReflection()->getFileName());

		$template = $this->getTemplate();
		$template->setFile($dir . '/default.latte');
		$template->render();
	}

	// </editor-fold>
}
