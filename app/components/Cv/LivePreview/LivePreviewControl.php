<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Model\Entity\Cv;

/**
 * Live Preview Control
 */
class LivePreviewControl extends BaseControl
{
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var Cv */
	private $cv;

	/** @var int */
	private $startPage = 1;

	/** @var float */
	private $scale = 0.7;

	/** @var float */
	private $scaleStep = 0.1;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	/** @return self */
	public function setScale($scale)
	{
		$this->scale = $scale;
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

	/** @return Cv */
	private function getCv()
	{
		if (!$this->cv) {
			throw new CvControlException('Must use method setCv(\App\Model\Entity\Cv)');
		}
		return $this->cv;
	}

	// </editor-fold>

	public function render()
	{
		$cv = $this->getCv();
		$this->template->cv = $cv;
		$this->template->startPage = $cv->lastOpenedPreviewPage ? $cv->lastOpenedPreviewPage : $this->startPage;
		$this->template->scale = $cv->lastUsedPreviewScale ? $cv->lastUsedPreviewScale : $this->scale;
		$this->template->scaleStep = $this->scaleStep;
		parent::render();
	}

}

interface ILivePreviewControlFactory
{

	/** @return LivePreviewControl */
	function create();
}
