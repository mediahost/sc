<?php

namespace App\Templating;

class TemplateFactory extends \Nette\Bridges\ApplicationLatte\TemplateFactory
{
	
	/**
	 * @param \Nette\Application\UI\Control $control
	 * @return \Nette\Bridges\ApplicationLatte\Template
	 */
	public function createTemplate(\Nette\Application\UI\Control $control)
	{
		
		$template = parent::createTemplate($control);
		$latte = $template->getLatte();
		$latte->onCompile[] = $this->addMacros;
		return $template;
	}
	
	public function addMacros(\Latte\Engine $latte)
	{
		$set = new \Latte\Macros\MacroSet($latte->getCompiler());
		$set->addMacro('ifCurrentIn', $this->ifCurrentInBegin, 'endif; unset($_c);');
	}
	
	public function ifCurrentInBegin($node, $writer)
	{
		return $writer->write('foreach (%node.array as $l) { if ($_presenter->isLinkCurrent($l)) { $_c = true; break; }} if (isset($_c)): ');
	}

}
