<?php

namespace App\Forms\Controls;

use Nette\Utils\Html;

/**
 * Spinner
 *
 * @author Petr Poupě
 */
class Spinner extends \Nette\Forms\Controls\TextInput
{

    private $attributes = array();
    private $readonly = TRUE;
    private $leftButtUp = FALSE;
    private $leftButtIcon = "minus";
    private $leftButtColor = "red";
    private $rightButtUp = TRUE;
    private $rightButtIcon = "plus";
    private $rightButtColor = "green";

    public function __construct($label = NULL, $rows = NULL)
    {
        parent::__construct($label);
    }

    /**
     * Generates control's HTML element.
     */
    public function getControl()
    {
        $block = Html::el('div')
                ->class("input-group", TRUE)
                ->add($this->getLeftButton())
                ->add($this->getInput())
                ->add($this->getRightButton());
        return Html::el('div class="form-spinner"')
                        ->add($block)
                        ->addAttributes($this->attributes);
    }

    private function getInput()
    {
        $input = Html::el('input class="spinner-input form-control"')
                ->name($this->getHtmlName())
                ->id($this->getHtmlId())
                ->value($this->getValue());
        if ($this->readonly) {
            $input->readonly("readonly");
        }
        return $input;
    }

    private function getLeftButton()
    {
        $icon = Html::el("i")->class("fa");
        if ($this->leftButtIcon) {
            $icon->class("fa-" . $this->leftButtIcon, TRUE);
        }
        $button = Html::el('button type="button"')
                ->class("btn {$this->leftButtColor}")
                ->class("spinner-" . ($this->leftButtUp ? "up" : "down"), TRUE)
                ->add($icon);
        return Html::el('div class="spinner-buttons input-group-btn"')
                        ->add($button);
    }

    private function getRightButton()
    {
        $icon = Html::el("i")->class("fa");
        if ($this->rightButtIcon) {
            $icon->class("fa-" . $this->rightButtIcon, TRUE);
        }
        $button = Html::el('button type="button"')
                ->class("btn {$this->rightButtColor}")
                ->class("spinner-" . ($this->rightButtUp ? "up" : "down"), TRUE)
                ->add($icon);
        return Html::el('div class="spinner-buttons input-group-btn"')
                        ->add($button);
    }

    /**
     * 
     * @param type $value
     * @return self
     */
    public function setReadOnly($value = TRUE)
    {
        $this->readonly = $value;
        return $this;
    }

    /**
     * 
     * @param type $value
     * @return self
     */
    public function setDisabled($value = TRUE)
    {
        $this->attributes["data-disabled"] = $value ? "true" : "false";
        $this->setReadOnly($value);
        return parent::setDisabled($value);
    }

    /**
     * 
     * @param type $value
     * @return self
     */
    public function setValue($value)
    {
        $this->attributes["data-value"] = $value;
        return parent::setValue($value);
    }

    /**
     * 
     * @param type $value
     * @return self
     */
    public function setMin($value)
    {
        $this->attributes["data-min"] = $value;
        return $this;
    }

    /**
     * 
     * @param type $value
     * @return self
     */
    public function setMax($value)
    {
        $this->attributes["data-max"] = $value;
        return $this;
    }

    /**
     * 
     * @param type $value
     * @return self
     */
    public function setStep($value)
    {
        $this->attributes["data-step"] = $value;
        return $this;
    }

    /**
     * 
     * @param type $value
     * @return self
     */
    public function setInverse($inverse = TRUE)
    {
        if ($inverse) {
            $this->leftButtUp = TRUE;
        } else {
            $this->leftButtUp = FALSE;
        }
        $this->rightButtUp = !$this->leftButtUp;
        return $this;
    }

    /**
     * 
     * @param type $value
     * @return self
     */
    public function setLeftButton($color = NULL, $faIcon = NULL)
    {
        if ($color) {
            $this->leftButtColor = $color;
        }
        if ($faIcon) {
            $this->leftButtIcon = $faIcon;
        }
        return $this;
    }

    /**
     * 
     * @param type $value
     * @return self
     */
    public function setRightButton($color = NULL, $faIcon = NULL)
    {
        if ($color) {
            $this->rightButtColor = $color;
        }
        if ($faIcon) {
            $this->rightButtIcon = $faIcon;
        }
        return $this;
    }

}
