<?php

namespace App\Components\Candidate;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Candidate;
use Exception;
use Nette\Utils\ArrayHash;

class ProfileControl extends BaseControl
{

	/** @var Candidate */
	public $candidate;

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	
	public function render() {
		$this->template->genderList = Candidate::getGenderList();
		parent::render();
	}
	
	
	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());
		
		$form->addSelect('title', 'Title', Candidate::getTitleList());

		$form->addText('degreebefore', 'Degree in front of name', NULL, 50)
				->getControlPrototype()->class[] = 'input-small';

		$form->addText('firstname', 'First Name(s)', NULL, 100)
				->setRequired('Please enter your First Name(s).');
		
		$form->addText('middlename', 'Middle Name', NULL, 100);
		
		$form->addText('surname', 'Surname(s)', NULL, 100)
				->setRequired('Please enter your Surname(s).');

		$form->addText('degreeafter', 'Degree after name', NULL, 50)
				->getControlPrototype()->class[] = 'input-small';

		$form->addDatePicker('birthday', 'Birthday');
        
        $form->addText('tags', 'Tags')
                ->setAttribute('data-role', 'tagsinput')
				->getControlPrototype()->class[] = 'input-small';


		$form->addRadioList('gender', 'Gender', Candidate::getGenderList())
			->setDefaultValue('x');

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->save();
		$this->onAfterSave($this->candidate);
	}

	protected function load(ArrayHash $values)
	{
		//$this->candidate->title = $values->title;
		$this->candidate->firstname = $values->firstname;
		$this->candidate->middlename = $values->middlename;
		$this->candidate->surname = $values->surname;
		$this->candidate->birthday = $values->birthday;
		$this->candidate->gender = $values->gender;
		$this->candidate->degreeBefore = $values->degreebefore;
		$this->candidate->degreeAfter = $values->degreeafter;
        $this->candidate->tags = $values->tags;
		return $this;
	}

	protected function save()
	{
		$this->em->persist($this->candidate);
		$this->em->flush();
		return $this;
	}

	/** @return array */
	protected function getDefaults()
	{
		$values = [
			'title' => $this->candidate->title,
			'firstname' => $this->candidate->firstname,
			'middlename' => $this->candidate->middlename,
			'surname' => $this->candidate->surname,
			'birthday' => $this->candidate->birthday,
			'gender' => $this->candidate->gender,
			'degreebefore' => $this->candidate->degreeBefore,
			'degreeafter' => $this->candidate->degreeAfter,
            'tags' => $this->candidate->tags
		];
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->candidate) {
			throw new ProfileControlException('Use setCandidate(\App\Model\Entity\Candidate) before render');
		}
	}

	// <editor-fold desc="setters & getters">

	public function setCandidate(Candidate $candidate)
	{
		$this->candidate = $candidate;
		return $this;
	}

	// </editor-fold>
}

interface IProfileControlFactory
{

	/** @return ProfileControl */
	function create();
}
