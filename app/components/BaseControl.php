<?php

namespace App\Components;

use App\Model\Storage\SettingsStorage;
use Nette\Application\UI;
use Nette\Localization\ITranslator;

/**
 * BaseControl.
 * @author Martin Šifra <me@martinsifra.cz>
 */
abstract class BaseControl extends UI\Control
{
	
	const DEFAULT_TEMPLATE = 'default';

	/** @var SettingsStorage @inject */
	public $settings;

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
