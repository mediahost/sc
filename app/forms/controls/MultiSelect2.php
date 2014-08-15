<?php

namespace App\Forms\Controls;

/**
 * MultiSelect2
 *
 * @author Petr Poupě
 */
class MultiSelect2 extends \Nette\Forms\Controls\MultiSelectBox
{

    public function __construct($label = NULL, array $items = NULL)
    {
        parent::__construct($label, $items);
        $this->control->class = "multi-select";
    }

}
