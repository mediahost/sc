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

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addText('degreebefore', 'Degree in front of name', NULL, 50);
//				->getControlPrototype()->class[] = 'input-small';

		$form->addText('name', 'Name', NULL, 100)
				->setAttribute('placeholder', 'name and surename')
				->setRequired('Please enter your name.');

		$form->addText('degreeafter', 'Degree after name', NULL, 50);
//				->getControlPrototype()->class[] = 'input-small';

		$form->addDateInput('birthday', 'Birthday');

		$form->addRadioList('gender', 'Gender', Candidate::getGenderList());

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
		$this->candidate->name = $values->name;
		$this->candidate->birthday = $values->birthday;
		$this->candidate->gender = $values->gender;
		$this->candidate->degreeBefore = $values->degreebefore;
		$this->candidate->degreeAfter = $values->degreeafter;
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
			'name' => $this->candidate->name,
			'birthday' => $this->candidate->birthday,
			'gender' => $this->candidate->gender,
			'degreebefore' => $this->candidate->degreeBefore,
			'degreeafter' => $this->candidate->degreeAfter,
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

class ProfileControlException extends Exception
{

}

interface IProfileControlFactory
{

	/** @return ProfileControl */
	function create();
}
