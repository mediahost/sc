<?php

namespace App\Components\Candidate;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Candidate;
use Exception;
use Nette\Utils\ArrayHash;

/**
 * Form with skills settings.
 */
class ProfileControl extends BaseControl
{

	/** @var Candidate */
	public $candidate;
	
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();
		
		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer);

		$form->addText('name', 'Name')
				->setAttribute('placeholder', 'name and surename')
				->setRequired('Please enter your name.');

		$form->addDateInput('birthday', 'Birthday');

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$entityDao = $this->em->getDao(Candidate::getClassName());
		$saved = $entityDao->save($this->candidate);
		$this->onAfterSave($saved);
	}

	protected function load(ArrayHash $values)
	{
		$this->candidate->name = $values->name;
		$this->candidate->birthday = $values->birthday;
	}

	/** @return array */
	protected function getDefaults()
	{
		$values = [
			'name' => $this->candidate->name,
			'birthday' => $this->candidate->birthday,
		];
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->candidate) {
			throw new ProfileControlException('Use setJob(\App\Model\Entity\Job) before render');
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

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
