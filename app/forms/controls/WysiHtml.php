<?php

namespace App\Forms\Controls;

/**
 * WysiHtml
 *
 * @author Petr Poupě
 */
class WysiHtml extends \Nette\Forms\Controls\TextArea
{
    public function __construct($label = NULL, $rows = NULL)
    {
        parent::__construct($label);
        $this->control->class = "wysihtml5";
        if ($rows) {
            $this->control->rows = $rows;
        }
    }
}
