<?php

namespace App\Forms;

/**
 * IFormFactory Interface
 *
 * @author Petr Poupě
 */
interface IFormFactory extends \Venne\Forms\IFormFactory
{

    /**
     * @return Form
     */
    public function create();
}
