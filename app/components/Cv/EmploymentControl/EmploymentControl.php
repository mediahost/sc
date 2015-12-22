<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Cv;
use Nette\Utils\ArrayHash;

class EmploymentControl extends BaseControl
{
	/** @var array */
	public $onAfterSave = [];

	/** @var Cv */
	private $cv;


	/**
	 * Creates component Form
	 * @return Form
	 */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->getElementPrototype()->addClass('ajax sendOnChange');
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());

		$form->addGroup();
		$form->addCheckbox('show_job', 'Include to CV');
		$form->addDatePicker('available', 'Available from');
		$form->addTextArea('position', 'Desired job position');
		
		$form->addGroup('Salary expectations');
		$form->addCheckbox('show_salary', 'Mention salary');
		$form->addText('salary_from', 'From')
			->addRule(Form::RANGE, 'Enter positive number', array(0, NULL));
		$form->addText('salary_to', 'To')
			->addRule(Form::RANGE, 'Enter positive number', array(0, NULL));

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	/**
	 * Handler for onSuccess form's event
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$this->load($values);
		$this->save();
		$this->invalidateControl();
		$this->onAfterSave();
	}

	/**
	 * Fills Cv entity by form's values
	 * @param ArrayHash $values
	 * @return \App\Components\Cv\EmploymentControl
	 */
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

	/**
	 * Saves Cv entity
	 * @return \App\Components\Cv\EmploymentControl
	 */
	private function save()
	{
		$cvRepo = $this->em->getRepository(Cv::getClassName());
		$cvRepo->save($this->cv);
		return $this;
	}

	/**
	 * Gets default values from entity
	 * @return array
	 */
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

	/**
	 * Checks if Cv entity exists
	 * @throws CvControlException
	 */
	private function checkEntityExistsBeforeRender()
	{
		if (!$this->cv) {
			throw new CvControlException('Use setCv(\App\Model\Entity\Cv) before render');
		}
	}

	/**
	 * Seter for Cv entity
	 * @param Cv $cv
	 * @return \App\Components\Cv\WorksControl
	 */
	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}
}

/**
 * Definition IEmploymentControlFactory
 * 
 */
interface IEmploymentControlFactory
{

	/** @return EmploymentControl */
	function create();
}
