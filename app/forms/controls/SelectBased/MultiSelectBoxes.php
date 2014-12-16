<?php

namespace App\Forms\Controls\SelectBased;

/**
 * MultiSelectBoxes
 *
 * @author Petr Poupě
 */
class MultiSelectBoxes extends \Nette\Forms\Controls\MultiSelectBox
{

	public function __construct($label = NULL, array $items = NULL)
	{
		parent::__construct($label, $items);
		$this->control->class = 'multi-select';
	}

}
