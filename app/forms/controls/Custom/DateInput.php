<?php

namespace App\Forms\Controls\Custom;

use DateTime;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Forms\Helpers;
use Nette\Forms\IControl;
use Nette\Utils\Html;

/**
 * DateInput
 *
 * @author Petr Poupě
 */
class DateInput extends BaseControl
{

	private $day;
	private $month;
	private $year;

	public function __construct($label = NULL)
	{
		parent::__construct($label);
		$this->addRule(__CLASS__ . '::validateDate', 'Date is invalid.');
	}

	// <editor-fold defaultstate="collapsed" desc="setters">

	/**
	 *
	 * @param string $value
	 * @return self
	 */
	public function setValue($value)
	{
		if ($value) {
			$date = \Nette\DateTime::from($value);
			$this->day = $date->format('j');
			$this->month = $date->format('n');
			$this->year = $date->format('Y');
		} else {
			$this->day = $this->month = $this->year = NULL;
		}
		return $this;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">

	/**
	 * @return DateTime|NULL
	 */
	public function getValue()
	{
		return self::validateDate($this) ? date_create()->setDate($this->year, $this->month, $this->day) : NULL;
	}

	/**
	 * Generates control's HTML element.
	 */
	public function getControl()
	{
		$name = $this->getHtmlName();
		$months = [
			1 => 'January',
			2 => 'February',
			3 => 'March',
			4 => 'April',
			5 => 'May',
			6 => 'June',
			7 => 'July',
			8 => 'August',
			9 => 'September',
			10 => 'October',
			11 => 'November',
			12 => 'December',
		];
		return Html::el()
						->add(Html::el('input')->name($name . '[day]')->id($this->getHtmlId())->value($this->day))
						->add(Helpers::createSelectBox(
										$months, array('selected?' => $this->month)
								)->name($name . '[month]'))
						->add(Html::el('input')->name($name . '[year]')->value($this->year));
	}

	// </editor-fold>

	public function loadHttpData()
	{
		$this->day = $this->getHttpData(Form::DATA_LINE, '[day]');
		$this->month = $this->getHttpData(Form::DATA_LINE, '[month]');
		$this->year = $this->getHttpData(Form::DATA_LINE, '[year]');
	}

	/**
	 * @return bool
	 */
	public static function validateDate(IControl $control)
	{
		return checkdate($control->month, $control->day, $control->year);
	}

}
