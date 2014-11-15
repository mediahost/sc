<?php

namespace App\Forms\Renderers;

use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\CheckboxList;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Metronic horizontal style
 *
 * @author Petr Poupě
 */
class MetronicHorizontalFormRenderer extends MetronicFormRenderer
{

	private $labelWidth;
	private $inputWidth;

	public function __construct($labelWidth = "3", $inputWidth = "9")
	{
		parent::__construct();
		$this->setLabelWidth($labelWidth)
				->setInputWidth($inputWidth);
		$this->initWrapper();
	}

	private function setLabelWidth($width)
	{
		$this->labelWidth = (string) $width;
		return $this;
	}

	private function setInputWidth($width)
	{
		$this->inputWidth = (string) $width;
		return $this;
	}

	protected function initWrapper()
	{
		parent::initWrapper();
		$wrapper = 'div class="col-md-' . $this->inputWidth . '"';
		$wrapperWithOffset = 'div class="col-md-offset-' . $this->labelWidth . ' col-md-' . $this->inputWidth . '"';
		$wrapperWithCheckboxlist = 'div class="col-md-' . $this->inputWidth . ' checkbox-list"';
		$this->wrappers['control']['container'] = $wrapper;
		$this->wrappers['control.checkboxlist']['container'] = $wrapperWithCheckboxlist;
		$this->wrappers['control.checkbox']['container'] = $wrapperWithOffset;
		$this->wrappers['control.submit']['container'] = $wrapperWithOffset;
	}

	protected function customizeInitedForm(Form &$form)
	{
		parent::customizeInitedForm($form);

		$form->getElementPrototype()->class('form-horizontal');

		$usedPrimary = FALSE;
		foreach ($form->getControls() as $control) {
			
			$this->customizeStandardControl($control, $usedPrimary);
			
			if ($control->getLabelPrototype() instanceof Html && !$control instanceof Checkbox) {
				$control->getLabelPrototype()->class("col-md-{$this->labelWidth}", TRUE);
			}
		}
	}

}
