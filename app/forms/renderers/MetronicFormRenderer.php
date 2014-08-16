<?php

namespace App\Forms\Renderers;

use Nette,
    Tracy\Debugger as Debug;

/**
 * Converts a Form into the HTML output.
 * Changes:
 * - Move errors into body
 * - Move all buttons out of body
 *
 * @author     Petr Poupě
 */
class MetronicFormRenderer extends ExtendedFormRenderer
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
        $this->wrappers['form']['container'] = 'div class="form-body"';
        $this->wrappers['form']['actions'] = 'div class="form-actions fluid"';
        $this->wrappers['error']['container'] = 'div class="alert alert-danger"';
        $this->wrappers['error']['item'] = 'p';
        $this->wrappers['controls']['container'] = NULL;
        $this->wrappers['pair']['container'] = 'div class="form-group"';
        $this->wrappers['pair']['actions'] = NULL;
        $this->wrappers['pair']['.error'] = 'has-error';
        $this->wrappers['control']['container'] = "div class=\"col-md-{$this->inputWidth}\"";
        $this->wrappers['control']['actions'] = "div class=\"col-md-offset-{$this->labelWidth} col-md-{$this->inputWidth}\"";
        $this->wrappers['label']['container'] = NULL;
        $this->wrappers['label']['requiredsuffix'] = \Nette\Utils\Html::el('span class=required')->setText('*');
        $this->wrappers['control']['description'] = 'span class="help-block"';
        $this->wrappers['control']['errorcontainer'] = 'span class="help-block"';
    }

    protected function initFormWrapper()
    {
        $this->form->getElementPrototype()->class('form-horizontal');
        parent::initFormWrapper();
    }

    protected function customizeControl(&$control, &$usedPrimary)
    {
        if ($control->getLabelPrototype() instanceof \Nette\Utils\Html) {
            $control->getLabelPrototype()->class("col-md-{$this->labelWidth} control-label", TRUE);
        }

        if ($control instanceof \Nette\Forms\Controls\Button) {
            $control->getControlPrototype()->class(!$usedPrimary ? 'btn btn-primary' : 'btn btn-default', TRUE);
            $usedPrimary = TRUE;
        } else if ($control instanceof \Nette\Forms\Controls\TextBase ||
                $control instanceof \Nette\Forms\Controls\SelectBox ||
                $control instanceof \Nette\Forms\Controls\MultiSelectBox ||
                $control instanceof \App\Forms\Controls\DatePicker) {
            $control->getControlPrototype()->class('form-control', TRUE);
        } else if ($control instanceof \Nette\Forms\Controls\Checkbox ||
                $control instanceof \Nette\Forms\Controls\CheckboxList ||
                $control instanceof \Nette\Forms\Controls\RadioList) {
            $control->getSeparatorPrototype()
                    ->setName('div')
                    ->class($control->getControlPrototype()->type);
        }
    }

}
