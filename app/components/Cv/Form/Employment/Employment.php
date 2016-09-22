<?php

namespace App\Components\Cv;

use App\Forms\Form;
use App\Model\Entity\Cv;
use Nette\Utils\ArrayHash;

class Employment extends CvForm
{
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();
		$form = $this->createFormInstance();

		$form->addGroup();
		$form->addCheckbox('show_job', 'Include to CV');
		$form->addDatePicker('available', 'Available from');
		$form->addTextArea('position', 'Desired job position');

		$form->addGroup('Salary expectations');
		$form->addCheckbox('show_salary', 'Mention salary');
		$form->addText('salary_from', 'From');
		$form->addText('salary_to', 'To');

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
		$this->cv->desiredEmploymentIsPublic = $values->show_job;
		$this->cv->desiredPosition = $values->position;
		$this->cv->availableFrom = $values->available;
		$this->cv->salaryIsPublic = $values->show_salary;
		$this->cv->salaryFrom = $values->salary_from;
		$this->cv->salaryTo = $values->salary_to;
		return $this;
	}

	protected function getDefaults()
	{
		$values = [
			'show_job' => $this->cv->desiredEmploymentIsPublic,
			'position' => $this->cv->desiredPosition,
			'available' => $this->cv->availableFrom,
			'show_salary' => $this->cv->salaryIsPublic,
			'salary_from' => $this->cv->salaryFrom,
			'salary_to' => $this->cv->salaryTo,
		];
		return $values;
	}
}

interface IEmploymentFactory
{
	/** @return Employment */
	function create();
}
