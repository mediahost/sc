<?php

namespace App\Components;

use Nette\Application\UI;
use GettextTranslator\Gettext as Translator;
use Nette\Localization\ITranslator;


/**
 * BaseControl
 *
 * @author Martin Šifra <me@martinsifra.cz>
 */
abstract class BaseControl extends UI\Control
{

	/** @var Translator @inject*/
	public $translator;
	
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getTemplate()
	{
		$template = parent::getTemplate();
		$template->setTranslator($this->translator);
		return $template;
	}
	
	public function render()
	{
		$dir = dirname($this->getReflection()->getFileName());
		
		$template = $this->getTemplate();
		$template->setFile($dir . '/default.latte');
		$template->render();
	}

}
