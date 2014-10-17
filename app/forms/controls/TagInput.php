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
		$this->control->class = "select2";
	}

	// <editor-fold defaultstate="collapsed" desc="setters">

	/**
	 *
	 * @param array $tags
	 * @return TagInput
	 */
	public function setTags(array $tags)
	{
		$attr = "data-tags";
		$this->control->$attr = json_encode($tags);
		return $this;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">
	// </editor-fold>
}
