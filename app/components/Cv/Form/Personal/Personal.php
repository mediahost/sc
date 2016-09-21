<?php

namespace App\Components\Cv;

use App\Forms\Form;
use App\Model\Entity\Competences;
use App\Model\Entity\Cv;
use Nette\Utils\ArrayHash;

class Personal extends CvForm
{

	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();
		$form = $this->createFormInstance();

		$form->addTextArea('social', 'Social skills and competences');
		$form->addTextArea('organisation', 'Organisational skills and competences');
		$form->addTextArea('technical', 'Technical skills and competences');
		$form->addTextArea('artictic', 'Artistic skills and competences');
		$form->addTextArea('other', 'Other skills and competences');
		$form->addMultiSelect2('licenses', 'Driving licenses', Competences::getDrivingLicensesList());

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
		if (!$this->cv->competence) {
			$this->cv->competence = new Competences();
		}
		$this->cv->competence->social = $values->social;
		$this->cv->competence->organisation = $values->organisation;
		$this->cv->competence->technical = $values->technical;
		$this->cv->competence->artictic = $values->artictic;
		$this->cv->competence->other = $values->other;
		$this->cv->competence->drivingLicenses = $values->licenses;
		return $this;
	}

	protected function getDefaults()
	{
		$values = [];
		if ($this->cv->competence) {
			$values = [
				'social' => $this->cv->competence->social,
				'organisation' => $this->cv->competence->organisation,
				'technical' => $this->cv->competence->technical,
				'artictic' => $this->cv->competence->artictic,
				'other' => $this->cv->competence->other,
				'licenses' => $this->cv->competence->drivingLicenses,
			];
		}
		return $values;
	}
}

interface IPersonalFactory
{

	/** @return Personal */
	function create();
}
