<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Cv;
use Nette\Utils\ArrayHash;

class EmploymentControl extends BaseControl
{
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var Cv */
	private $cv;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();

		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addGroup();
		$form->addCheckSwitch('show_job', 'Include to CV')
				->setOnText('Yes')
				->setOffText('No');
		$form->addDatePicker('available', 'Available from');
		$form->addTextArea('position', 'Desired job position');
		
		$form->addGroup('Salary expectations');
		$form->addCheckSwitch('show_salary', 'Mention salary')
				->setOnText('Yes')
				->setOffText('No');
		$form->addTouchSpin('salary_from', 'From')
				->setStep(1000)
				->setMin(3000)
				->setMax(200000)
				->setPostfix('€')
				->setOption('description', '€ per annum');
		$form->addTouchSpin('salary_to', 'To')
				->setStep(1000)
				->setMin(3000)
				->setMax(200000)
				->setPostfix('€')
				->setOption('description', '€ per annum');

		if ($this->isAjax && $this->isSendOnChange) {
			$form->getElementPrototype()->class('ajax sendOnChange');
		} else {
			$form->addSubmit('save', 'Save');
		}

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$this->load($values);
		$this->save();
		$this->onAfterSave($this->cv);
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

	/** @return array */
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
			throw new CvControlException('Use setCv(\App\Model\Entity\Cv) before render');
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}

	// </editor-fold>
}

interface IEmploymentControlFactory
{

	/** @return EmploymentControl */
	function create();
}
