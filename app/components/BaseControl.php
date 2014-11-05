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
	// <editor-fold defaultstate="expanded" desc="constants & variables">
	
	const MIN_PASSWORD_CHARACTERS = 8;

	/** @var ITranslator @inject */
	public $translator;

	// </editor-fold>

	public function __construct()
	{
		parent::__construct();
	}

	// <editor-fold defaultstate="expanded" desc="setters">
	// </editor-fold>
	// <editor-fold defaultstate="expanded" desc="getters">

	public function getTemplate()
	{
		$template = parent::getTemplate();
		$template->setTranslator($this->translator);
		return $template;
	}

	// </editor-fold>
	// <editor-fold defaultstate="expanded" desc="renderers">

	public function render()
	{
		$dir = dirname($this->getReflection()->getFileName());

		$template = $this->getTemplate();
		$template->setFile($dir . '/default.latte');
		$template->render();
	}

	// </editor-fold>
}
