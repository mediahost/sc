<?php

namespace App\Components;

use Nette\Application\UI;
use GettextTranslator\Gettext as Translator;


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

}
