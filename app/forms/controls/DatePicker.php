<?php

namespace App\Forms\Controls;

use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;
use Nette\Utils\DateTime;
use Tracy\Debugger as Debug;

/**
 * DatePicker
 *
 * @author Petr Poupě
 */
class DatePicker extends BaseControl
{

    const SIZE_FLUID = NULL;
    const SIZE_XL = "input-xlarge";
    const SIZE_L = "input-large";
    const SIZE_M = "input-medium";
    const SIZE_S = "input-small";
    const SIZE_XS = "input-xsmall";

    private $date;
    private $format;
    private $size;
    private $readOnly = FALSE;
    private $attributes = array();

    public function __construct($label = NULL, $format = "d.m.Y")
    {
        parent::__construct($label);
        $this->format = $format;
        $this->attributes["data-date-format"] = \App\Helpers::dateformatPHP2JS($this->format);
        $this->addRule(__CLASS__ . '::validateDate', 'Date is invalid.');
    }

    /**
     * @return bool
     */
    public static function validateDate(\Nette\Forms\IControl $control)
    {
        if (!$control->isRequired() && empty($control->date)) {
            return TRUE;
        } else {
            $d = $control->date instanceof \DateTime ?
                    $control->date : DateTime::createFromFormat($control->format, $control->date);
            return $d && $d->format($control->format) == DateTime::from($control->date)->format($control->format);
        }
    }

    public function setValue($value)
    {
        if ($value) {
            $this->date = DateTime::from($value);
        } else {
            $this->date = NULL;
        }
    }

    /**
     * @return DateTime|string|NULL
     */
    public function getValue($formated = FALSE)
    {
        $date = $this->date instanceof \DateTime ?
                $this->date : DateTime::createFromFormat($this->format, $this->date);
        return self::validateDate($this) ? ($formated ? $date->format($this->format) : $date) : NULL;
    }

    public function loadHttpData()
    {
        $this->date = $this->getHttpData(\Nette\Forms\Form::DATA_LINE, '[date]');
    }

    /**
     * Generates control's HTML element.
     */
    public function getControl()
    {
        $input = $this->getInput(!$this->readOnly);
        $icon = $this->getIcon();
        $button = $this->getButton();

        $block = Html::el('div');
        $block->class($this->size, TRUE);
        if ($this->readOnly) {
            $block->class('input-group date date-picker', TRUE)
                    ->addAttributes($this->attributes)
                    ->add($input)
                    ->add($button);
        } else {
            $block->class('input-icon right', TRUE)
                    ->add($icon)
                    ->add($input->addAttributes($this->attributes));
        }
        return $block;
    }

    /**
     * 
     * @param type $size
     * @return self
     */
    public function setSize($size = self::SIZE_FLUID)
    {
        switch ($size) {
            case self::SIZE_FLUID:
            case self::SIZE_XL:
            case self::SIZE_L:
            case self::SIZE_M:
            case self::SIZE_S:
            case self::SIZE_XS:
                $this->size = $size;
                break;
            default:
                $this->size = self::SIZE_FLUID;
                break;
        }
        return $this;
    }

    public function setReadOnly($value = TRUE)
    {
        $this->readOnly = $value;
        return $this;
    }

    private function getInput($picker = TRUE)
    {
        $input = Html::el('input class="form-control"')
                ->name($this->getHtmlName() . '[date]')
                ->id($this->getHtmlId())
                ->value($this->getValue(TRUE));
        if ($picker) {
            $input->class("date-picker", TRUE);
        }
        if ($this->readOnly) {
            $input->readonly("readonly");
        }
        return $input;
    }

    private function getIcon()
    {
        return Html::el('i class="fa fa-calendar"');
    }

    private function getButton()
    {
        return Html::el('span class="input-group-btn"')
                        ->add(Html::el('button class="btn default" type="button"')
                                ->add($this->getIcon()));
    }

    /**
     * 
     * @param DateTime $value
     * @return self
     */
    public function setStartDate(DateTime $value)
    {
        $this->attributes["data-start-date"] = $value->format($this->format);
        return $this;
    }

    /**
     * 
     * @param DateTime $value
     * @return self
     */
    public function setEndDate(DateTime $value)
    {
        $this->attributes["data-end-date"] = $value->format($this->format);
        return $this;
    }

    /**
     * Set a limit for the view mode. 
     * Accepts: “days” or 0, “months” or 1, and “years” or 2. 
     * Gives the ability to pick only a month or an year. 
     * The day is set to the 1st for “months”, and the month is set to January for “years”.
     * @param type $value
     * @return self
     */
    public function setMinViewMode($value)
    {
        $this->attributes["data-min-view-mode"] = $value;
        return $this;
    }

    /**
     * 
     * @param bool $value
     * @return self
     */
    public function setTodayHighlight($value = TRUE)
    {
        $this->attributes["data-today-highlight"] = $value ? "true" : "false";
        return $this;
    }

    /**
     * 
     * @param string $value
     * @return self
     */
    public function setPlaceholder($value)
    {
        $this->attributes["placeholder"] = $value;
        return $this;
    }

}
