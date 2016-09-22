<?php

namespace App\Components\Cv;

use App\Forms\Form;
use Nette\Utils\ArrayHash;

class Objective extends CvForm
{
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();
		$form = $this->createFormInstance();
		$form->addCheckbox('show', 'Include in CV');
		$form->addTextArea('objective', 'Your career objective');
		$form->setDefaults($this->getDefaults());
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$this->load($values);
		$this->save();
		$this->redrawControl();
		$this->onAfterSave();
	}

	private function load(ArrayHash $values)
	{
		$this->cv->careerObjective = $values->objective;
		$this->cv->careerObjectiveIsPublic = $values->show;
		return $this;
	}

	protected function getDefaults()
	{
		$values = [
			'objective' => $this->cv->careerObjective,
			'show' => $this->cv->careerObjectiveIsPublic,
		];
		return $values;
	}
}

interface IObjectiveFactory
{

	/** @return Objective */
	function create();
}
