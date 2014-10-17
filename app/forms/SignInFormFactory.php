<?php

namespace App\Forms;

/**
 * SignIn FormFactory
 *
 * @author Petr Poupě
 */
class SignInFormFactory extends FormFactory
{

	// <editor-fold defaultstate="collapsed" desc="constants & variables">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="create">
	public function create()
	{
		$form = $this->formFactory->create();

		$form->addText('username', 'Username')
//                ->setRequired('Please enter your username')
				->setAttribute('placeholder', 'Username');
		$form->addPassword('password', 'Password')
//                ->setRequired('Please enter your password')
				->setAttribute('placeholder', 'Password');

		$form->addCheckbox('remember', 'Remember me')
						->getLabelPrototype()->class = "checkbox";

		$form->addSubmit('_submit', 'Login');
		return $form;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="public">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="private">
	// </editor-fold>
}
