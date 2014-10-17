<?php

namespace App\Forms;

/**
 * Parent FormFactory
 *
 * @author Petr Poupě
 */
abstract class FormFactory implements IFormFactory
{
	// <editor-fold defaultstate="collapsed" desc="constants & variables">

	/** @var \App\Forms\IFormFactory */
	protected $formFactory;

	/** @var bool */
	protected $add = FALSE;

	// </editor-fold>

	public function __construct(IFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}

	// <editor-fold defaultstate="collapsed" desc="create">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="public">

	public function setAdding($add = TRUE)
	{
		$this->add = $add;
		return $this;
	}

	public function isAdding()
	{
		return $this->add;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="private">
	// </editor-fold>
}
