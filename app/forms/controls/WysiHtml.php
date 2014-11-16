<?php

namespace App\Forms\Controls;

/**
 * WysiHtml
 *
 * @author Petr Poupě
 */
class WysiHtml extends \Nette\Forms\Controls\TextArea
{

	// <editor-fold defaultstate="collapsed" desc="constants & variables">
	// </editor-fold>

	public function __construct($label = NULL, $rows = NULL)
	{
		parent::__construct($label);
		$this->control->class = 'wysihtml5';
		if ($rows) {
			$this->control->rows = $rows;
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">
	// </editor-fold>
}
