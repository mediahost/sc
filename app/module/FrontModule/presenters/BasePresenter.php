<?php

namespace App\FrontModule\Presenters;

abstract class BasePresenter extends \App\BaseModule\Presenters\BasePresenter
{

	public function getPureName()
	{
		$pos = strrpos($this->name, ':');
		if (is_int($pos)) {
			return substr($this->name, $pos + 1);
		}

		return $this->name;
	}

	protected function setDemoLayout()
	{
		$this->setLayout('demo');
		$this->template->pacePluginOff = TRUE;
	}

	/** @return \WebLoader\Nette\CssLoader */
	protected function createComponentCssDemo()
	{
		$css = $this->webLoader->createCssLoader('demo')
				->setMedia('screen,projection,tv');
		return $css;
	}

}
