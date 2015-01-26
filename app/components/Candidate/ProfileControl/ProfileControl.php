<?php

namespace App\Components\Candidate;

use App\Components\EntityControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Candidate;
use Exception;
use Nette\Utils\ArrayHash;

/**
 * Form with skills settings.
 */
class ProfileControl extends EntityControl
{
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
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
		$entity = $this->load($values);
		$entityDao = $this->em->getDao(Candidate::getClassName());
		$saved = $entityDao->save($entity);
		$this->onAfterSave($saved);
	}

	/**
	 * Load Entity from Form
	 * @param ArrayHash $values
	 * @return Candidate
	 */
	protected function load(ArrayHash $values)
	{
		$entity = $this->getEntity();
		$entity->name = $values->name;
		$entity->birthday = $values->birthday;
		return $entity;
	}

	/**
	 * Get Entity for Form
	 * @return array
	 */
	protected function getDefaults()
	{
		$entity = $this->getEntity();
		$values = [
			'name' => $entity->name,
			'birthday' => $entity->birthday,
		];
		return $values;
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	protected function checkEntityType($entity)
	{
		return $entity instanceof Candidate;
	}

	/** @throwsProfileControlExceptionn */
	protected function getNewEntity()
	{
		throw new ProfileControlException('Must use method setEntity(\App\Model\Entity\Candidate)');
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
