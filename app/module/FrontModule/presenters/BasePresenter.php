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

}
