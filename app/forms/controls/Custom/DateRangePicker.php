<?php

namespace App\Forms\Controls\Custom;

use Nette\Forms\Controls\BaseControl;
use App\Helpers;
use Nette\Utils\Html;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\Utils\DateTime;

/**
 * Description of DateRangePicker
 *
 */
class DateRangePicker extends BaseControl  {
	
	/** @var DateTime */
	private $date_start;
	
	/** @var DateTime */
	private $date_end;
	
	/** @var string */
	private $format;
	
	/** @var array */
	private $attributes = [];
	
	
	public function __construct($label = NULL, $format = 'd.m.Y')
	{
		parent::__construct($label);
		$this->format = $format;
		$this->attributes['data-date-format'] = Helpers::dateformatPHP2JS($this->format);
		$this->addRule(__CLASS__ . '::validateDate', 'Date is invalid.');
	}
	
	/**
	 * Set control value (date_start and date_end)
	 * @param array $value
	 */
	public function setValue($value) {
		if($value['start']) {
			$this->date_start = DateTime::from($value['start']);
		}
		if($value['end']) {
			$this->date_end = DateTime::from($value['end']);
		}
	}
	
	/**
	 * Get control value
	 * @param bool $formated
	 * @return DateTime|string|NULL
	 */
	public function getValue($formated = FALSE) {
		$start = $this->date_start instanceof \DateTime ?
				$this->date_start : DateTime::createFromFormat($this->format, $this->date_start);
		$end = $this->date_end instanceof \DateTime ?
				$this->date_end : DateTime::createFromFormat($this->format, $this->date_end);
		
		if(!self::validateDate($this)) {
			return NULL;
		}
		
		return [
			'start' => $start  ?  ($formated  ?  $start->format($this->format) : $start)  :  NULL, 
			'end' => $end  ?  ($formated  ?  $end->format($this->format) : $end)   :   NULL
		];
	}
	
	/**
	 * Generates control's HTML element.
	 * @return Html object
	 */
	public function getControl() {
		$value = $this->getValue(TRUE);
		$input_start = $this->getInput()->name($this->getHtmlName() . '[date_start]')->value($value['start']);
		$input_end = $this->getInput()->name($this->getHtmlName() . '[date_end]')->value($value['end']);
		$block = Html::el('div class="input-daterange input-group"')
			->addAttributes($this->attributes)
			->add($this->getIcon())
			->add($input_start)
			->add($this->getEndLabel())
			->add($input_end);
		
		return $block;
	}
	
	/**
	 * Generates input's HTML for control
	 * @return Html object
	 */
	private function getInput() {
		$input = Html::el('input class="form-control"');
		return $input;
	}
	
	/**
	 * Generates icon's HTML for control
	 * @return Html object
	 */
	protected function getIcon()
	{
		return Html::el('span class="input-group-addon">')
			->add(Html::el('i class="fa fa-calendar"'));
	}
	
	/**
	 * Generates end label HTML for control
	 * @return Html object
	 */
	protected function getEndLabel() {
		return Html::el('span class="input-group-addon">')
			->add('to');
	}
	
	/**
	 * Loads HTTP data.
	 */
	public function loadHttpData()
	{
		$this->date_start = $this->getHttpData(Form::DATA_LINE, '[date_start]');
		$this->date_end = $this->getHttpData(Form::DATA_LINE, '[date_end]');
	}
	
	/**
	 * Validate control (date_start and date_end)
	 * @param \App\Forms\Controls\Custom\IControl $control
	 * @return boolean
	 */
	public static function validateDate(IControl $control)
	{
		$start = $control->date_start instanceof \DateTime ?
			$control->date_start : DateTime::createFromFormat($control->format, $control->date_start);
		$end = $control->date_end instanceof \DateTime ?
			$control->date_end : DateTime::createFromFormat($control->format, $control->date_end);

		$validStart = $control->date_start == null  ||  $control->date_start == ''  ||
			($start && $start->format($control->format) == DateTime::from($control->date_start)->format($control->format));

		$validEnd = $control->date_end == null  ||  $control->date_end == ''  ||
			($end && $end->format($control->format) == DateTime::from($control->date_end)->format($control->format));

		return $validStart && $validEnd;
	}
}
