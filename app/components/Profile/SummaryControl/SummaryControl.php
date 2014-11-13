<?php

namespace App\Components\Profile;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;
use Nette\Utils\ArrayHash;

class SummaryControl extends BaseControl
{

	public $onSuccess = [];

	/** @var SignUpStorage @inject */
	public $session;

	/** @var UserFacade @inject */
	public $userFacade;

	public function beforeRender()
	{
		$this->template->role = $this->session->role;
	}

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form;
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		$form->addSubmit('complete', 'Complete Registration');

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$this->onSuccess($this, $this->session->user);
	}

}

interface ISummaryControlFactory
{

	/** @return AdditionalControl */
	function create();
}
