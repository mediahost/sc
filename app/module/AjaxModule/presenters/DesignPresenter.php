<?php

namespace App\AjaxModule\Presenters;

/**
 * Ajax design
 */
class DesignPresenter extends BasePresenter
{

	public function actionSetColor($color)
	{
		if ($this->designService->isAllowedColor($color)) {
			$this->designService->color = $color;
			$this->addData('color', $color);
		} else {
			$this->setError('This color isn\'t supported.');
		}
	}

}
