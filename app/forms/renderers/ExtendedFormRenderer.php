<?php

namespace App\Forms\Renderers;

use Nette\Forms\Form;
use Nette\Forms\Rendering\DefaultFormRenderer;

/**
 * Converts a Form into the HTML output.
 * Extended about new abstract functions
 *
 * @author     Petr Poupě
 */
class ExtendedFormRenderer extends DefaultFormRenderer
{
	// <editor-fold defaultstate="expanded" desc="customize functions">

	/**
	 * After initializating form
	 */
	protected function customizeInitedForm(Form &$form)
	{
		
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="overrrides functions">

	public function __construct()
	{
		$this->wrappers['form']['body'] = NULL;
	}

	/**
	 * Provides complete form rendering.
	 * @param  Form
	 * @param  string 'begin', 'errors', 'ownerrors', 'body', 'end' or empty to render all
	 * @return string
	 */
	public function render(Form $form, $mode = NULL)
	{
		if ($this->form !== $form) {
			$this->form = $form;
			$this->init();
		}

		// START EDITED
		$this->customizeInitedForm($this->form);
		// END EDITED

		$s = '';
		if (!$mode || $mode === 'begin') {
			$s .= $this->renderBegin();
		}
		if (!$mode || strtolower($mode) === 'ownerrors') {
			$s .= $this->renderErrors();
		} elseif ($mode === 'errors') {
			$s .= $this->renderErrors(NULL, FALSE);
		}
		if (!$mode || $mode === 'body') {
			$s .= $this->renderBody();
		}
		if (!$mode || $mode === 'end') {
			$s .= $this->renderEnd();
		}
		return $s;
	}

	// </editor-fold>
}
