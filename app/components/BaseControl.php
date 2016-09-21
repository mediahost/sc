<?php

namespace App\Components;

use App\Extensions\Settings\SettingsStorage;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
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

	protected function createFormInstance() {
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());
		if ($this->isAjax && $this->isSendOnChange) {
			$form->getElementPrototype()->class('ajax sendOnChange');
		} else {
			$form->addSubmit('save', 'Save');
		}
		return $form;
	}
}

class BaseControlException extends \Exception
{

}
