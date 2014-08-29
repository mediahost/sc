<?php

namespace App\Components;

use GettextTranslator\Gettext as Translator;

/**
 * Control
 *
 * @author Petr Poupě
 */
class Control extends \Nette\Application\UI\Control
{

	/** @var Translator */
	public $translator;

	public function __construct(Translator $translator)
	{
		parent::__construct();
		$this->translator = $translator;
	}
	
	public function getTemplate()
	{
		$template = parent::getTemplate();
		$template->setTranslator($this->translator);
		return $template;
	}

}
