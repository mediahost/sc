<?php

namespace App\Templating;

use Latte\Engine;
use Latte\Macros\MacroSet;
use Nette\Localization\ITranslator;
use Nette\Application\UI\Control;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Bridges\ApplicationLatte\TemplateFactory as ParentTemplateFactory;

class TemplateFactory extends ParentTemplateFactory
{

	/** @var ITranslator */
	protected $translator;

	public function injectTranslator(ITranslator $translator)
	{
		$this->translator = $translator;
	}

	/**
	 * @param Control $control
	 * @return Template
	 */
	public function createTemplate(Control $control = NULL)
	{
		$template = parent::createTemplate($control);
		$latte = $template->getLatte();
		$latte->onCompile[] = $this->addMacros;
		$template->setTranslator($this->translator);
		return $template;
	}

	public function addMacros(Engine $latte)
	{
		$set = new MacroSet($latte->getCompiler());
		$set->addMacro('ifCurrentIn', $this->ifCurrentInBegin, 'endif; unset($_c);');
	}

	public function ifCurrentInBegin($node, $writer)
	{
		return $writer->write('foreach (%node.array as $l) { if ($_presenter->isLinkCurrent($l)) { $_c = true; break; }} if (isset($_c)): ');
	}

}
