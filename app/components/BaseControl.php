<?php

namespace App\Components;

use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;

/**
 * BaseControl
 * @author Martin Šifra <me@martinsifra.cz>
 * Features: Automatic set of default.latte template in current directory.
 */
abstract class BaseControl extends Control
{

	/** @var ITranslator @inject */
	public $translator;
	
	public function __construct()
	{
		parent::__construct();
	}

	public function getTemplate()
	{
		return parent::getTemplate()
						->setTranslator($this->translator);
	}
	
	public function beforeRender()
	{
		
	}

	public function render()
	{
		$dir = dirname($this->getReflection()->getFileName());

		$this->template->setFile($dir . \DIRECTORY_SEPARATOR . 'default.latte');
		$this->beforeRender();
		$this->template->render();
	}

}
