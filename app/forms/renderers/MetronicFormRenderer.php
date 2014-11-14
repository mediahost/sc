<?php

namespace App\Forms\Renderers;

use App\Forms\Controls\DatePicker;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\CheckboxList;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextBase;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Metronic style
 *
 * @author     Petr Poupě
 */
class MetronicFormRenderer extends ExtendedFormRenderer
{

	public function __construct()
	{
		$this->initWrapper();
	}

	protected function initWrapper()
	{
		$this->wrappers['form']['container'] = 'div class="form-body"';
		$this->wrappers['error']['container'] = 'div class="alert alert-danger"';
		$this->wrappers['error']['item'] = 'p';
		$this->wrappers['controls']['container'] = NULL;
		$this->wrappers['pair']['container'] = 'div class="form-group"';
		$this->wrappers['pair']['.error'] = 'has-error';
		$this->wrappers['label']['container'] = NULL;
		$this->wrappers['label']['requiredsuffix'] = Html::el('span class=required')->setText('*');
		$this->wrappers['control']['description'] = 'span class="help-block"';
		$this->wrappers['control']['errorcontainer'] = 'span class="help-block"';
	}

	protected function customizeInitedForm(Form &$form)
	{
		parent::customizeInitedForm($form);

		$usedPrimary = FALSE;
		foreach ($form->getControls() as $control) {
			if ($control->getLabelPrototype() instanceof Html) {
				$control->getLabelPrototype()->class("control-label", TRUE);
			}

			if ($control instanceof Button) {
				$control->getControlPrototype()->class(!$usedPrimary ? 'btn btn-primary' : 'btn btn-default', TRUE);
				$usedPrimary = TRUE;
			} else if ($control instanceof TextBase ||
					$control instanceof SelectBox ||
					$control instanceof MultiSelectBox ||
					$control instanceof DatePicker) {
				$control->getControlPrototype()->class('form-control', TRUE);
			} else if ($control instanceof Checkbox ||
					$control instanceof CheckboxList ||
					$control instanceof RadioList) {
				$control->getSeparatorPrototype()
						->setName('div')
						->class($control->getControlPrototype()->type);
			}
		}
	}

}
