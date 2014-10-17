<?php

namespace App\Forms\Controls;

/**
 * Select2
 *
 * @author Petr Poupě
 */
class Select2 extends \Nette\Forms\Controls\SelectBox
{

	// <editor-fold defaultstate="collapsed" desc="constants & variables">
	// </editor-fold>

	public function __construct($label = NULL, array $items = NULL)
	{
		parent::__construct($label, $items);
		$this->control->class = "select2";
	}

	// <editor-fold defaultstate="collapsed" desc="setters">

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

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">
	// </editor-fold>
}
