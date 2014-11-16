<?php

namespace App\Forms\Controls;

/**
 * MultiSelect2
 *
 * @author Petr Poupě
 */
class MultiSelect2 extends \Nette\Forms\Controls\MultiSelectBox
{

	// <editor-fold defaultstate="collapsed" desc="constants & variables">
	// </editor-fold>

	public function __construct($label = NULL, array $items = NULL)
	{
		parent::__construct($label, $items);
		$this->control->class = 'multi-select';
	}

	// <editor-fold defaultstate="collapsed" desc="setters">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">
	// </editor-fold>
}
