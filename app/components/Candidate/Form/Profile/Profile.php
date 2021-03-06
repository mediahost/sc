<?php

namespace App\Components\Candidate;

use App\Components\BaseControl;
use App\Components\BaseControlException;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Person;
use App\Model\Entity\Role;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

class Profile extends BaseControl
{

	/** @var array */
	public $onAfterSave = [];

	/** @var User @inject */
	public $user;

	/** @var Person */
	public $person;

	/** @var bool */
	private $editable = FALSE;

	public function render()
	{
		$this->template->genderList = Person::getGenderList();
		parent::render();
	}

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addSelect('title', 'Title', Person::getTitleList());

		$form->addText('degreebefore', 'Degree in front of name', NULL, 50)
			->getControlPrototype()->class[] = 'input-small';

		$form->addText('firstname', 'First Name(s)', NULL, 100)
			->setRequired('Please enter your First Name(s).');

		$form->addText('middlename', 'Middle Name', NULL, 100);

		$surname = $form->addText('surname', 'Surname(s)', NULL, 100);
		if ($this->user->isInRole(Role::CANDIDATE)) {
			$surname->setRequired('Please enter your Surname(s).');
		}

		$form->addText('degreeafter', 'Degree after name', NULL, 50)
			->getControlPrototype()->class[] = 'input-small';

		$form->addDatePicker('birthday', 'Birthday');

		$form->addText('phone', 'Mobile number');

		$form->addText('mail2', '2nd e-mail');

		$form->addRadioList('gender', 'Gender', Person::getGenderList())
			->setDefaultValue('x');

		if ($this->person->getCandidate()->id) {
			$form->addText('tags', 'Tags')
				->setAttribute('data-role', 'tagsinput')
				->getControlPrototype()->class[] = 'input-small';
		}

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->save();
		$this->onAfterSave($this->person);
	}

	protected function load(ArrayHash $values)
	{
		$this->person->firstname = $values->firstname;
		$this->person->middlename = $values->middlename;
		$this->person->surname = $values->surname;
		$this->person->birthday = $values->birthday;
		$this->person->gender = $values->gender;
		$this->person->degreeBefore = $values->degreebefore;
		$this->person->degreeAfter = $values->degreeafter;
		$this->person->phoneMobile = $values->phone;
		$this->person->mailHome = $values->mail2;
		if (isset($values->tags) && $values->tags) {
			$this->person->getCandidate()->tags = $values->tags;
		}
		return $this;
	}

	protected function save()
	{
		$this->em->persist($this->person);
		$this->em->flush();
		return $this;
	}

	protected function getDefaults()
	{
		$values = [
			'title' => $this->person->title,
			'firstname' => $this->person->firstname,
			'middlename' => $this->person->middlename,
			'surname' => $this->person->surname,
			'birthday' => $this->person->birthday,
			'gender' => $this->person->gender,
			'degreebefore' => $this->person->degreeBefore,
			'degreeafter' => $this->person->degreeAfter,
			'phone' => $this->person->phoneMobile,
			'mail2' => $this->person->mailHome,
			'tags' => $this->person->getCandidate()->tags
		];
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->person) {
			throw new BaseControlException('Use setPerson(\App\Model\Entity\Person) before render');
		}
	}

	// <editor-fold desc="setters & getters">

	public function setPerson(Person $person)
	{
		$this->person = $person;
		return $this;
	}

	public function canEdit($value = TRUE)
	{
		$this->editable = $value;
		return $this;
	}

	// </editor-fold>
}

interface IProfileFactory
{

	/** @return Profile */
	function create();
}
