<?php

namespace App\Forms\Controls;

/**
 * Select2
 *
 * @author Petr Poupě
 */
class Select2 extends \Nette\Forms\Controls\SelectBox
{

    public function __construct($label = NULL, array $items = NULL)
    {
        parent::__construct($label, $items);
        $this->control->class = "select2";
    }
    
    /**
     * 
     * @param type $prompt
     * @return self
     */
    public function setPrompt($prompt)
    {
        $this->setPlaceholder($prompt);
        $attr = "data-allow_clear";
        $this->control->$attr = 'true';
        return parent::setPrompt('');
    }

    /**
     * @deprecated Use setPrompt() instead
     * @param type $value
     * @return self
     */
    public function setPlaceholder($value)
    {
        $attr = "data-placeholder";
        $this->control->$attr = $value;
        return $this;
    }

}
