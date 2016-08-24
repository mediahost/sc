<?php

namespace App\Components;

use App\Extensions\Settings\SettingsStorage;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI;
use Nette\Localization\ITranslator;

abstract class BaseControl extends UI\Control
{

	const DEFAULT_TEMPLATE = 'default';

	/** @var EntityManager @inject */
	public $em;

	/** @var SettingsStorage @inject */
	public $settings;

	/** @var ITranslator @inject */
	public $translator;

	/** @var string */
	private $templateFile = self::DEFAULT_TEMPLATE;

	/** @var bool */
	protected $isAjax = FALSE;

	/** @var bool */
	protected $isSendOnChange = FALSE;

	public function __construct()
	{
		parent::__construct();
	}

	protected function setTemplateFile($name)
	{
		$this->templateFile = $name;
		return $this;
	}

	/**
	 * Set ajax for form
	 */
	public function setAjax($isAjax = TRUE, $sendOnChange = TRUE)
	{
		$this->isAjax = $isAjax;
		$this->isSendOnChange = $sendOnChange;
		return $this;
	}

	public function render()
	{
		$dir = dirname($this->getReflection()->getFileName());

		$template = $this->getTemplate();
		$template->setFile($dir . '/' . $this->templateFile . '.latte');
		$template->render();
	}

}
