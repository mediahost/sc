<?php

namespace App\Components\Cv;

use App\Forms\Form;
use Nette\Utils\ArrayHash;

class Additional extends CvForm
{
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();
		$form = $this->createFormInstance();
		$form->addCheckbox('show', 'Include in CV');
		$form->addTextArea('additional', 'Additional information');
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
		$this->cv->additionalInfo = $values->additional;
		$this->cv->additionalIsPublic = $values->show;
		return $this;
	}

	protected function getDefaults()
	{
		$values = [
			'additional' => $this->cv->additionalInfo,
			'show' => $this->cv->additionalIsPublic
		];
		return $values;
	}
}

interface IAdditionalFactory
{

	/** @return Additional */
	function create();
}
