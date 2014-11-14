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

	const MIN_PASSWORD_CHARACTERS = 8;
	const DEFAULT_TEMPLATE = 'default';

	/** @var ITranslator @inject */
	public $translator;
	
	/** @var string */
	private $templateFile = self::DEFAULT_TEMPLATE;

	public function __construct()
	{
		parent::__construct();
	}
	
	protected function setTemplateFile($name)
	{
		$this->templateFile = $name;
		return $this;
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
		$template->setFile($dir . '/' . $this->templateFile . '.latte');
		$template->render();
	}

}
