<?php

namespace App\Forms;

use App\Forms\Controls\ChoiseBased\CheckboxList;
use App\Forms\Controls\ChoiseBased\CheckSwitch;
use App\Forms\Controls\ChoiseBased\RadioList;
use App\Forms\Controls\Custom\DateInput;
use App\Forms\Controls\Custom\DatePicker;
use App\Forms\Controls\SelectBased\MultiSelect2;
use App\Forms\Controls\SelectBased\Select2;
use App\Forms\Controls\TextAreaBased\WysiHtml;
use App\Forms\Controls\TextInputBased\Spinner;
use App\Forms\Controls\TextInputBased\TagInput;
use App\Forms\Controls\TextInputBased\TouchSpin;
use Nette\Application\UI\Form as BaseForm;

/**
 * Form
 *
 * @author Petr Poupě
 */
class Form extends BaseForm
{
	// <editor-fold defaultstate="expanded" desc="Special controls">

	/**
	 *
	 * @param type $name
	 * @param type $caption
	 * @return DateInput
	 */
	public function addDateInput($name, $caption = NULL)
	{
		return $this[$name] = new DateInput($caption);
	}

	/**
	 *
	 * @param type $name
	 * @param type $caption
	 * @return TagInput
	 */
	public function addTagInput($name, $caption = NULL)
	{
		return $this[$name] = new TagInput($caption);
	}

	/**
	 *
	 * @param type $name
	 * @param type $caption
	 * @return DatePicker
	 */
	public function addDatePicker($name, $caption = NULL)
	{
		return $this[$name] = new DatePicker($caption);
	}

	/**
	 *
	 * @param type $name
	 * @param type $caption
	 * @param type $rows
	 * @return WysiHtml
	 */
	public function addWysiHtml($name, $caption = NULL, $rows = NULL)
	{
		return $this[$name] = new WysiHtml($caption, $rows);
	}

	/**
	 *
	 * @param type $name
	 * @param type $caption
	 * @param type $onText
	 * @param type $offText
	 * @return CheckSwitch
	 */
	public function addCheckSwitch($name, $caption = NULL, $onText = NULL, $offText = NULL)
	{
		return $this[$name] = new CheckSwitch($caption, $onText, $offText);
	}

	/**
	 *
	 * @param type $name
	 * @param type $caption
	 * @return TouchSpin
	 */
	public function addTouchSpin($name, $caption = NULL)
	{
		return $this[$name] = new TouchSpin($caption);
	}

	/**
	 *
	 * @param type $name
	 * @param type $caption
	 * @return Spinner
	 */
	public function addSpinner($name, $caption = NULL)
	{
		return $this[$name] = new Spinner($caption);
	}

	/**
	 *
	 * @param type $name
	 * @param type $label
	 * @return Select2
	 */
	public function addSelect2($name, $label = NULL, array $items = NULL)
	{
		return $this[$name] = new Select2($label, $items);
	}

	/**
	 *
	 * @param type $name
	 * @param type $label
	 * @return MultiSelect2
	 */
	public function addMultiSelect2($name, $label = NULL, array $items = NULL)
	{
		return $this[$name] = new MultiSelect2($label, $items);
	}

	/**
	 * Adds set of radio button controls to the form.
	 * @param  string  control name
	 * @param  string  label
	 * @param  array   options from which to choose
	 * @return RadioList
	 */
	public function addRadioList($name, $label = NULL, array $items = NULL)
	{
		return $this[$name] = new RadioList($label, $items);
	}

	/**
	 * Adds set of checkbox controls to the form.
	 * @return CheckboxList
	 */
	public function addCheckboxList($name, $label = NULL, array $items = NULL)
	{
		return $this[$name] = new CheckboxList($label, $items);
	}

	// </editor-fold>
}
