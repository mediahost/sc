<?php

namespace App\Components\Job;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Job;
use App\Model\Facade\CompanyFacade;
use App\Model\Facade\JobFacade;
use Nette\Utils\ArrayHash;

/**
 * Job info form
 */
class BasicInfoControl extends BaseControl
{
	const SALARY_FROM = 0;
	const SALARY_TO = 10000;
	const SALARY_STEP = 10;

	/** @var Job */
	private $job;

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var CompanyFacade @inject */
	public $companyFacade;
	
	/** @var JobFacade @inject */
	public $jobFacade;

	// </editor-fold>
	// <editor-fold desc="variables">
	// </editor-fold>
    
   
    public function render() {
        $this->template->job = $this->job;
        parent::render();
    }
    
    public function handleEdit() {
        $this->setTemplateFile('edit');
        $this->redrawControl('jobInfo');
    }
    
    public function handlePreview() {
        $this->redrawControl('jobInfo');
    }

	/** @return Form */
	protected function createComponentForm()
	{
		$defaultValues = $this->getDefaults();
		$this->checkEntityExistsBeforeRender();

		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer);

		$form->addText('name', 'Name')
				->setAttribute('placeholder', 'Job title')
				->setRequired('Please enter job\'s name.');
		$form->addSelect('type', 'Type', $this->jobFacade->getJobTypes());
		$form->addSelect('category', 'Category', $this->jobFacade->getJobCategories());
		$form->addText('salary', 'Salary')
			->setAttribute('class', 'slider')
			->setAttribute('data-slider-min', self::SALARY_FROM)
			->setAttribute('data-slider-max', self::SALARY_TO)
			->setAttribute('data-slider-step', self::SALARY_STEP)
			->setAttribute('data-slider-value', $defaultValues['salary'])
			->setAttribute('data-slider-id', 'slider-primary');
		$form->addMapView('location', 'Location');

		$form->addSubmit('save', 'Save');

		$form->setDefaults($defaultValues);
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values, $form);
		$this->save();
		$this->onAfterSave($this->job);
	}

	protected function load(ArrayHash $values, Form $form)
	{
		sscanf($values['salary'], '%d,%d', $salaryFrom, $salaryTo);
		$this->job->name = $values->name;
		$this->job->type = $this->jobFacade->findJobType($values->type);
		$this->job->category = $this->jobFacade->findJobCategory($values->category);
		$this->job->salaryFrom = $salaryFrom;
		$this->job->salaryTo = $salaryTo;
		$this->job->location = $form['location']->getValue();
		return $this;
	}

	private function save()
	{
		$cvRepo = $this->em->getRepository(Job::getClassName());
		$cvRepo->save($this->job);
		return $this;
	}

	/** @return array */
	protected function getDefaults()
	{
		$salaryFrom = isset($this->job->salaryFrom)  ?  $this->job->salaryFrom : self::SALARY_FROM;
		$salaryTo = isset($this->job->salaryTo)  ?  $this->job->salaryTo : self::SALARY_TO;
		$salary = sprintf('[%d,%d]', $salaryFrom, $salaryTo);
		$values = [
			'name' => $this->job->name,
			'type' => $this->job->type  ?  $this->job->type->id : NULL,
			'category' => $this->job->category  ?  $this->job->category->id : NULL,
			'salary' => $salary,
			'location' => $this->job->location
		];
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->job) {
			throw new JobControlException('Use setJob(\App\Model\Entity\Job) before render');
		}
	}

	// <editor-fold desc="setters & getters">

	public function setJob(Job $job)
	{
		$this->job = $job;
		return $this;
	}

	// </editor-fold>
}

interface IBasicInfoControlFactory
{

	/** @return BasicInfoControl */
	function create();
}
