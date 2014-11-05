<?php

namespace App\Forms;

/**
 * Form
 *
 * @author Petr Poupě
 */
class Form extends \Nette\Application\UI\Form
{
	
	/** @var array */
	public $onAfterSuccess;
	
	/** @var array */
	private $onEnd;
	
	/** @var array */
	public $onSaveButton;
	
	/** @var array */
	public $onContinueButton;
	
	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		$this->onValidate[] = function () {
			$this->onSuccess[] = function (Form $form, $values) {
				if ($this->onAfterSuccess) {
					foreach ($this->onAfterSuccess as $handler) {
						\Nette\Utils\Callback::invoke($handler, $this, $values);
					}
				}
				if ($this->onEnd) {
					foreach ($this->onEnd as $handler) {
						\Nette\Utils\Callback::invoke($handler, $this, $values);
					}
				}
			};
		};
		parent::__construct($parent, $name);
	}
	
	public function addDefaultSubmits()
	{
		$this->addSubmit('_submit', 'Save');
		$this->addSubmit('submitContinue', 'Save and continue edit');
		$this->onEnd[] = function (Form $form, $values) {
			if ($form['submitContinue']->submittedBy) {
				if ($this->onContinueButton) {
					foreach ($this->onContinueButton as $handler) {
						\Nette\Utils\Callback::invoke($handler, $this, $values);
					}
				}
			} elseif ($form['_submit']->submittedBy) {
				if ($this->onSaveButton) {
					foreach ($this->onSaveButton as $handler) {
						\Nette\Utils\Callback::invoke($handler, $this, $values);
					}
				}
			}
		};
	}
	
	// <editor-fold defaultstate="collapsed" desc="special items">

	/**
	 *
	 * @param type $name
	 * @param type $caption
	 * @return Controls\DateInput
	 */
	public function addDateInput($name, $caption = NULL)
	{
		return $this[$name] = new Controls\DateInput($caption);
	}

	/**
	 *
	 * @param type $name
	 * @param type $caption
	 * @return Controls\TagInput
	 */
	public function addTagInput($name, $caption = NULL)
	{
		return $this[$name] = new Controls\TagInput($caption);
	}

	/**
	 *
	 * @param type $name
	 * @param type $caption
	 * @return Controls\DatePicker
	 */
	public function addDatePicker($name, $caption = NULL)
	{
		return $this[$name] = new Controls\DatePicker($caption);
	}

	/**
	 *
	 * @param type $name
	 * @param type $caption
	 * @param type $rows
	 * @return Controls\WysiHtml
	 */
	public function addWysiHtml($name, $caption = NULL, $rows = NULL)
	{
		return $this[$name] = new Controls\WysiHtml($caption, $rows);
	}

	/**
	 *
	 * @param type $name
	 * @param type $caption
	 * @param type $onText
	 * @param type $offText
	 * @return Controls\CheckSwitch
	 */
	public function addCheckSwitch($name, $caption = NULL, $onText = NULL, $offText = NULL)
	{
		return $this[$name] = new Controls\CheckSwitch($caption, $onText, $offText);
	}

	/**
	 *
	 * @param type $name
	 * @param type $caption
	 * @return Controls\TouchSpin
	 */
	public function addTouchSpin($name, $caption = NULL)
	{
		return $this[$name] = new Controls\TouchSpin($caption);
	}

	/**
	 *
	 * @param type $name
	 * @param type $caption
	 * @return Controls\Spinner
	 */
	public function addSpinner($name, $caption = NULL)
	{
		return $this[$name] = new Controls\Spinner($caption);
	}

	/**
	 *
	 * @param type $name
	 * @param type $label
	 * @return Controls\Select2
	 */
	public function addSelect2($name, $label = NULL, array $items = NULL)
	{
		return $this[$name] = new Controls\Select2($label, $items);
	}

	/**
	 *
	 * @param type $name
	 * @param type $label
	 * @return Controls\MultiSelect2
	 */
	public function addMultiSelect2($name, $label = NULL, array $items = NULL)
	{
		return $this[$name] = new Controls\MultiSelect2($label, $items);
	}

	// </editor-fold>
}
