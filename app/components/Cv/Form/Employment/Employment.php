<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Cv;
use Nette\Utils\ArrayHash;

class Employment extends BaseControl
{
	/** @var array */
	public $onAfterSave = [];

	/** @var Cv */
	private $cv;

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
		$this->cv->desiredEmploymentIsPublic = $values->show_job;
		$this->cv->desiredPosition = $values->position;
		$this->cv->availableFrom = $values->available;
		$this->cv->salaryIsPublic = $values->show_salary;
		$this->cv->salaryFrom = $values->salary_from;
		$this->cv->salaryTo = $values->salary_to;
		return $this;
	}

	private function save()
	{
		$cvRepo = $this->em->getRepository(Cv::getClassName());
		$cvRepo->save($this->cv);
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

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->cv) {
			throw new CvException('Use setCv(\App\Model\Entity\Cv) before render');
		}
	}

	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}
}

interface IEmploymentFactory
{

	/** @return Employment */
	function create();
}
