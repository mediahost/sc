<?php

namespace App\Components\AfterRegistration;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicHorizontalFormRenderer;
use App\Model\Entity\Address;
use App\Model\Entity\Person;
use App\Model\Entity\User;
use App\Model\Facade\UserFacade;
use Nette\Http\FileUpload;
use Nette\Utils\ArrayHash;

class CompletePerson extends BaseControl
{

	// <editor-fold desc="events">

	/** @var array */
	public $onSuccess = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var \Nette\Security\User @inject */
	public $user;

	// </editor-fold>

	public function render()
	{
		$this->setTemplateFile('person');
		parent::render();
	}

	protected function createComponentForm()
	{
		/* @var $person Person */
		$user = $this->user->getIdentity();
		$person = $user->getPerson();

		$form = new Form();
		$form->setRenderer(new MetronicHorizontalFormRenderer());
		$form->setTranslator($this->translator);

		$form->addText('firstName', 'First Name(s)')
			->setRequired();
		$form->addText('middleName', 'Middle Name');
		$form->addText('surname', 'Surname(s)')
			->setRequired();

		$defaultsArr = [
			'firstName' => $person->firstname,
			'middleName' => $person->middlename,
			'surname' => $person->surname,
		];
		$form->setDefaults($defaultsArr);

		$form->addSubmit('save', 'Continue');

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$userRepo = $this->em->getRepository(User::getClassName());

		/* @var $user User */
		$user = $this->user->getIdentity();
		$person = $user->getPerson();

		$person->firstname = $values->firstName;
		$person->middlename = $values->middleName;
		$person->surname = $values->surname;

		$userRepo->save($user);

		$this->onSuccess($this, $person);
	}

}

interface ICompletePersonFactory
{

	/** @return CompletePerson */
	function create();
}
