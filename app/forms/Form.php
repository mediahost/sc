<?php

namespace App\Forms;

use Nette\Application\UI\Form as BaseForm;

/**
 * Form
 *
 * @author Petr Poupě
 */
class Form extends BaseForm
{
	
	use AddControls;

	/**
	 * Adds naming container to the form.
	 * @param  string  name
	 * @return Container
	 */
	public function addContainer($name)
	{
		$control = new Container;
		$control->currentGroup = $this->currentGroup;
		return $this[$name] = $control;
	}

}
