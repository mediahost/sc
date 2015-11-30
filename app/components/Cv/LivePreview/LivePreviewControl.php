<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Model\Entity\Cv;

/**
 * Live Preview Control
 */
class LivePreviewControl extends BaseControl
{
	// <editor-fold desc="variables">

	/** @var Cv */
	private $cv;

	/** @var int */
	private $startPage = 1;

	/** @var float */
	private $scale = 0.8;

	/** @var float */
	private $scaleStep = 0.1;

	/** @var float */
	private $scaleMin = 0.6;

	/** @var float */
	private $scaleMax = 1;

	// </editor-fold>
	// <editor-fold desc="setters & getters">

	/** @return self */
	public function setScale($scale, $scaleMin = NULL, $scaleMax = NULL)
	{
		$this->scale = $scale;
		if ($scaleMin) {
			$this->scaleMin = $scaleMin;
		}
		if ($scaleMax) {
			$this->scaleMax = $scaleMax;
		}
		return $this;
	}

	/** @return self */
	public function setScaleStep($step)
	{
		$this->scaleStep = $step;
		return $this;
	}

	/** @return self */
	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}

	// </editor-fold>

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->cv) {
			throw new CvControlException('Use setCv(\App\Model\Entity\Cv) before render');
		}
	}

	public function render()
	{
		$this->checkEntityExistsBeforeRender();
		$this->template->cv = $this->cv;
		$this->template->startPage = $this->cv->lastOpenedPreviewPage ? $this->cv->lastOpenedPreviewPage : $this->startPage;
		$this->template->scale = $this->cv->lastUsedPreviewScale ? $this->cv->lastUsedPreviewScale : $this->scale;
		$this->template->scaleStep = $this->scaleStep;
		$this->template->scaleMin = $this->scaleMin;
		$this->template->scaleMax = $this->scaleMax;
		parent::render();
	}

}

interface ILivePreviewControlFactory
{

	/** @return LivePreviewControl */
	function create();
}
