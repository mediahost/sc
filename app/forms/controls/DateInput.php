<?php

namespace App\Forms\Controls;

use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * DateInput
 *
 * @author Petr Poupě
 */
class DateInput extends BaseControl
{

    private $day, $month, $year;

    public function __construct($label = NULL)
    {
        parent::__construct($label);
        $this->addRule(__CLASS__ . '::validateDate', 'Date is invalid.');
    }

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

    /**
     * @return DateTime|NULL
     */
    public function getValue()
    {
        return self::validateDate($this) ? date_create()->setDate($this->year, $this->month, $this->day) : NULL;
    }

    public function loadHttpData()
    {
        $this->day = $this->getHttpData(Form::DATA_LINE, '[day]');
        $this->month = $this->getHttpData(Form::DATA_LINE, '[month]');
        $this->year = $this->getHttpData(Form::DATA_LINE, '[year]');
    }

    /**
     * Generates control's HTML element.
     */
    public function getControl()
    {
        $name = $this->getHtmlName();
        return Html::el()
                        ->add(Html::el('input')->name($name . '[day]')->id($this->getHtmlId())->value($this->day))
                        ->add(\Nette\Forms\Helpers::createSelectBox(
                                        array("zimní měsíce" => array(1 => "leden", 2), "jarní měsíce" => array(3 => 3, 4, 5, 6, 7, 8, 9, 10, 11, 12)), array('selected?' => $this->month)
                                )->name($name . '[month]'))
                        ->add(Html::el('input')->name($name . '[year]')->value($this->year));
    }

    /**
     * @return bool
     */
    public static function validateDate(\Nette\Forms\IControl $control)
    {
        return checkdate($control->month, $control->day, $control->year);
    }

}
