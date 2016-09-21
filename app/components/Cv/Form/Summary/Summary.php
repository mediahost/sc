<?php

namespace App\Components\Cv;

use App\Forms\Form;
use App\Model\Entity\Cv;
use Nette\Utils\ArrayHash;

class Summary extends CvForm
{

	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();
		$form = $this->createFormInstance();

		$form->addCheckbox('show', 'Include in CV');
		$form->addTextArea('summary', 'Career summary');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
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
		$this->cv->careerSummary = $values->summary;
		$this->cv->careerSummaryIsPublic = $values->show;
		return $this;
	}

	protected function getDefaults()
	{
		$values = [
			'summary' => $this->cv->careerSummary,
			'show' => $this->cv->careerSummaryIsPublic,
		];
		return $values;
	}
}

interface ISummaryFactory
{

	/** @return Summary */
	function create();
}
