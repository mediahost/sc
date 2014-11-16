<?php

namespace App\Forms\Controls;

/**
 * TagInput
 *
 * @author Petr Poupě
 */
class TagInput extends \Nette\Forms\Controls\TextInput
{

	// <editor-fold defaultstate="collapsed" desc="constants & variables">
	// </editor-fold>

	public function __construct($label = NULL)
	{
		parent::__construct($label);
		$this->control->class = 'tags';
	}

	// <editor-fold defaultstate="collapsed" desc="setters">
	
	/**
	 *
	 * @param string $text
	 * @return self
	 */
	public function setPlaceholder($text)
	{
		$attr = 'data-defaultText';
		$this->control->$attr = $text;
		return $this;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">
	// </editor-fold>
}
