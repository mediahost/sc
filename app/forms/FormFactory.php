<?php

namespace App\Forms;

/**
 * Parent FormFactory
 *
 * @author Petr Poupě
 */
abstract class FormFactory implements IFormFactory
{

    /** @var \App\Forms\IFormFactory */
    protected $formFactory;

    /** @var bool */
    protected $add = FALSE;

    public function __construct(IFormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function setAdding($add = TRUE)
    {
        $this->add = $add;
        return $this;
    }

    public function isAdding()
    {
        return $this->add;
    }

}
