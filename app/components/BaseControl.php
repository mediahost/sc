<?php

namespace App\Components;

use App\Extensions\Settings\SettingsStorage;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Translation\Translator;
use Nette\Application\UI;

abstract class BaseControl extends UI\Control
{

	const DEFAULT_TEMPLATE = 'default';

	/** @var EntityManager @inject */
	public $em;

	/** @var SettingsStorage @inject */
	public $settings;

	/** @var Translator @inject */
	public $translator;

	/** @var string */
	private $templateFile = self::DEFAULT_TEMPLATE;

	/** @var bool */
	protected $isAjax = FALSE;

	/** @var bool */
	protected $isSendOnChange = FALSE;

	/** @var bool */
	protected $isModal = FALSE;

	public function __construct()
	{
		parent::__construct();
	}

	protected function setTemplateFile($name)
	{
		$this->templateFile = $name;
		return $this;
	}

	public function setAjax($isAjax = TRUE, $sendOnChange = TRUE)
	{
		$this->isAjax = $isAjax;
		$this->isSendOnChange = $sendOnChange;
		return $this;
	}

	public function setModal($isModal = TRUE)
	{
		$this->isModal = $isModal;
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
		$this->template->isModal = $this->isModal;

		$dir = dirname($this->getReflection()->getFileName());

		$template = $this->getTemplate();
		$template->setFile($dir . '/' . $this->templateFile . '.latte');
		$template->render();
	}
}

class BaseControlException extends \Exception
{

}
