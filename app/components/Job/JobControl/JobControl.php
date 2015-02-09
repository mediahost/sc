<?php

namespace App\Components\Job;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Job;
use App\Model\Facade\CompanyFacade;
use Exception;
use Nette\Utils\ArrayHash;

/**
 * Job info form
 */
class JobControl extends BaseControl
{

	/** @var Job */
	public $job;

	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="injects">

	/** @var CompanyFacade @inject */
	public $companyFacade;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var bool */
	private $canEditInfo = FALSE;

	/** @var bool */
	private $canEditSkills = FALSE;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer);

//		if ($this->canEditInfo) {
			$form->addText('name', 'Name')
					->setAttribute('placeholder', 'Job title')
					->setRequired('Please enter job\'s name.');
			$form->addTextArea('description', 'Description')
					->setAttribute('placeholder', 'Job description');
//		}

//		if ($this->canEditSkills) {
			
//		}

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$job = $this->load($values);
		$jobDao = $this->em->getDao(Job::getClassName());
		$savedJob = $jobDao->save($job);
		$this->onAfterSave($savedJob);
	}

	/** @return Job */
	protected function load(ArrayHash $values)
	{
//		if ($this->canEditInfo) {
			$this->job->name = $values->name;
			$this->job->description = $values->description;
//		}
//		if ($this->canEditSkills) {
			
//		}
		return $this->job;
	}

	/** @return array */
	protected function getDefaults()
	{
		$values = [
			'name' => $this->job->name,
			'description' => $this->job->description,
		];
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if ($this->job === NULL) {
			throw new JobControlException('Use setJob() before render');
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	public function setJob(Job $job)
	{
		$this->job = $job;
		return $this;
	}

	public function setCanEditInfo($value = TRUE)
	{
		$this->canEditInfo = $value;
		return $this;
	}

	public function setCanEditSkills($value = TRUE)
	{
		$this->canEditSkills = $value;
		return $this;
	}

	// </editor-fold>
}

class JobControlException extends Exception
{
	
}

interface IJobControlFactory
{

	/** @return JobControl */
	function create();
}
