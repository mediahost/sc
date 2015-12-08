<?php

namespace App\Forms\Renderers;

use App\Forms\Renderers\ExtendedFormRenderer;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\CheckboxList;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextBase;
use Nette\Forms\Form;
use Nette\Utils\Html;

class Bootstrap3FormRenderer extends ExtendedFormRenderer
{

	public function __construct()
	{
		$this->initWrapper();
	}

	protected function initWrapper()
	{
		$this->wrappers['controls']['container'] = NULL;
		$this->wrappers['pair']['container'] = 'div class=form-group';
		$this->wrappers['pair']['.error'] = 'has-error';
		$this->wrappers['control']['container'] = 'div class="col-lg-10 col-md-9"';
		$this->wrappers['label']['container'] = '';
		$this->wrappers['label']['requiredsuffix'] = Html::el('span class=required')->setText('*');
		$this->wrappers['control']['description'] = 'span class=help-block';
		$this->wrappers['control']['errorcontainer'] = 'span class=help-block';
		$this->wrappers['control.submit']['container'] = 'div class="col-md-offset-2 col-lg-10 col-md-9"';
		$this->wrappers['control.checkbox']['container'] = 'div class="col-md-offset-2 col-lg-10 col-md-9"';
	}

	protected function customizeInitedForm(Form &$form)
	{
		parent::customizeInitedForm($form);

		$form->getElementPrototype()->addClass('form-horizontal group-border stripped');

		foreach ($form->getControls() as $control) {
			if ($control instanceof Button) {
				$control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
				$usedPrimary = TRUE;
			} elseif ($control instanceof TextBase || $control instanceof SelectBox || $control instanceof MultiSelectBox) {
				$control->getControlPrototype()->addClass('form-control');
			} elseif ($control instanceof Checkbox || $control instanceof CheckboxList || $control instanceof RadioList) {
				$control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
			}
		}
	}

	public function renderLabel(\Nette\Forms\IControl $control) {
		$suffix = $this->getValue('label suffix') . ($control->isRequired() ? $this->getValue('label requiredsuffix') : '');
		$label = $control->getLabel();
		if ($label instanceof Html) {
			$label->add($suffix);
			if ($control->isRequired()) {
				$label->class($this->getValue('control .required'), TRUE);
			}
			$label->addClass('col-lg-2 col-md-3 control-label');
		} elseif ($label != NULL) { // @intentionally ==
			$label .= $suffix;
		}
		return $this->getWrapper('label container')->setHtml($label);
	}
}
