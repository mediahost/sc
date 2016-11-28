<?php

namespace App\Components\Job;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Model\Entity\Match;
use Nette\Utils\ArrayHash;

class CustomState extends BaseControl
{

	/** @var Match */
	private $match;

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>

	public function render()
	{
		parent::render();
	}

	protected function createComponentForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		if ($this->isAjax) {
			$form->getElementPrototype()->class[] = 'ajax';
		}
		if ($this->isSendOnChange) {
			$form->getElementPrototype()->class[] = 'sendOnChange';
		}

		$customValue = $this->match->customState;
		$form->addText('state')
			->setDefaultValue($customValue)
			->getControlPrototype()->class = 'form-control';

		$form->addSubmit('send', 'Custom Status')
			->getControlPrototype()->class[] = 'btn ' . ($customValue ? 'btn-info' : 'btn-default');

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values, $form);
		$this->save();
		$this->onAfterSave($this->match);
	}

	protected function load(ArrayHash $values, Form $form)
	{
		$this->match->customState = $values->state;
		return $this;
	}

	private function save()
	{
		$matchRepo = $this->em->getRepository(Match::getClassName());
		$matchRepo->save($this->match);
		return $this;
	}

	// <editor-fold desc="setters & getters">

	public function setMatch(Match $match)
	{
		$this->match = $match;
		return $this;
	}

	// </editor-fold>
}

interface ICustomStateFactory
{

	/** @return CustomState */
	function create();
}
