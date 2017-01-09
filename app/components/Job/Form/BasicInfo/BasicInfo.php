<?php

namespace App\Components\Job;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Job;
use App\Model\Entity\Role;
use App\Model\Entity\Tag;
use App\Model\Entity\TagJob;
use App\Model\Entity\User;
use App\Model\Facade\CompanyFacade;
use App\Model\Facade\JobFacade;
use Nette\Utils\ArrayHash;

class BasicInfo extends BaseControl
{

	const SALARY_FROM = 0;
	const SALARY_TO = 10000;
	const SALARY_STEP = 100;
	const QUESTIONS_COUNT = 5;

	/** @var Job */
	private $job;

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var \Nette\Security\User @inject */
	public $user;

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var JobFacade @inject */
	public $jobFacade;

	// </editor-fold>

	public function render()
	{
		$this->template->job = $this->job;
		parent::render();
	}

	protected function createComponentForm()
	{
		$defaultValues = $this->getDefaults();
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());

		$form->addGroup('Basic Info');
		$form->addText('name', 'Name')
			->setAttribute('placeholder', 'Job title')
			->setRequired('Please enter job\'s name.');
		$form->addText('wp_id', 'Wordpress ID')
			->addRule(Form::INTEGER, 'Id must be numeric value')
			->setAttribute('placeholder', 'Wordpress ID');

		if ($this->user->isAllowed('job', 'accountManager')) {
			$form->addSelect('accountManager', 'Account manager', $this->getAccountManagers())
				->setRequired($this->translator->translate('Manager is required'));
		}

		$form->addSelect('type', 'Type', $this->jobFacade->getJobTypes())
			->setRequired($this->translator->translate('Type is required'));
		$form->addSelect('category', 'Category', $this->jobFacade->getJobCategories())
			->setRequired($this->translator->translate('Category is required'));
		$form->addText('salary', 'Salary (per mth)')
			->setAttribute('class', 'slider')
			->setAttribute('data-slider-min', self::SALARY_FROM)
			->setAttribute('data-slider-max', self::SALARY_TO)
			->setAttribute('data-slider-step', self::SALARY_STEP)
			->setAttribute('data-slider-value', $defaultValues['salary'])
			->setAttribute('data-slider-id', 'slider-primary');
		$form->addText('offers', 'Benefits')
			->setAttribute('data-role', 'tagsinput')
			->setAttribute('placeholder', 'add a tag');
		$form->addText('requirements', 'Technical Requirements')
			->setAttribute('data-role', 'tagsinput')
			->setAttribute('placeholder', 'add a tag');

		$form->addGroup('Job Description');
		$form->addWysiHtml('summary', 'Position Summary')
			->setAttribute('placeholder', 'Job summary')
			->setAttribute('id', 'jobSummary');
		$form->addWysiHtml('description', 'Position Description')
			->setAttribute('placeholder', 'Job description')
			->setAttribute('id', 'jobDescription');

		$form->addGroup('Pre-Screening Questions');
		$questions = $form->addContainer('questions');
		for ($i = 1; $i <= self::QUESTIONS_COUNT; $i++) {
			$questions->addText($i, 'Question ' . $i);
		}

//		$form->addGroup('Notes');
//		$form->addTextArea('notes')->setAttribute('rows', '8');

		if (!$this->job->isNew()) {
			$form->addSubmit('save', 'Save');
		}
		$form->addSubmit('next', 'Save & Next');

		$form->setDefaults($defaultValues);
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values, $form);
		$this->save();
		$this->onAfterSave($this->job, $form['next']->submittedBy);
	}

	protected function load(ArrayHash $values, Form $form)
	{
		$this->job->name = $values->name;
		$this->job->wordpressId = $values->wp_id;
		$this->job->type = $this->jobFacade->findJobType($values->type);
		$this->job->category = $this->jobFacade->findJobCategory($values->category);
//		$this->job->notes = $values->notes;
		$this->job->questions = [];
		foreach ($values->questions as $question) {
			if ($question && !empty($question)) {
				$this->job->questions[] = $question;
			}
		}

		$salaryFrom = $salaryTo = 0;
		sscanf($values['salary'], '%d,%d', $salaryFrom, $salaryTo);
		$this->job->salaryFrom = $salaryFrom;
		$this->job->salaryTo = $salaryTo;

		$accountManager = $this->getAccountManager(isset($values->accountManager) ? $values->accountManager : NULL);
		$this->job->accountManager = $accountManager;

		foreach (explode(',', $values['offers']) as $offer) {
			if ($offer != '') {
				$tagJob = $this->createTagIfNotExits($offer, TagJob::TYPE_OFFERS);
				$this->job->tag = $tagJob;
			}
		}
		foreach (explode(',', $values['requirements']) as $requirement) {
			if ($requirement != '') {
				$tagJob = $this->createTagIfNotExits($requirement, TagJob::TYPE_REQUIREMENTS);
				$this->job->tag = $tagJob;
			}
		}
		$this->job->removeOldTags();

		if (isset($values->description)) {
			$this->job->description = $values->description;
		}
		if (isset($values->summary)) {
			$this->job->summary = $values->summary;
		}

		return $this;
	}

	private function save()
	{
		$cvRepo = $this->em->getRepository(Job::getClassName());
		$cvRepo->save($this->job);
		return $this;
	}

	protected function getDefaults()
	{
		$salaryFrom = isset($this->job->salaryFrom) ? $this->job->salaryFrom : self::SALARY_FROM;
		$salaryTo = isset($this->job->salaryTo) ? $this->job->salaryTo : self::SALARY_TO;
		$salary = sprintf('[%d,%d]', $salaryFrom, $salaryTo);
		$values = [
			'name' => $this->job->name,
			'wp_id' => $this->job->wordpressId,
			'type' => $this->job->type ? $this->job->type->id : NULL,
			'category' => $this->job->category ? $this->job->category->id : NULL,
			'salary' => $salary,
			'accountManager' => $this->job->accountManager ? $this->job->accountManager->id : NULL,
			'offers' => $this->getTags(TagJob::TYPE_OFFERS),
			'requirements' => $this->getTags(TagJob::TYPE_REQUIREMENTS),
//			'notes' => $this->job->notes,
			'description' => $this->job->description,
			'summary' => $this->job->summary,
		];
		if ($this->job->questions) {
			$i = 1;
			foreach ($this->job->questions as $question) {
				$values['questions'][$i] = $question;
				$i++;
			}
		}
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->job) {
			throw new JobException('Use setJob(\App\Model\Entity\Job) before render');
		}
	}

	// <editor-fold desc="setters & getters">

	public function setJob(Job $job)
	{
		$this->job = $job;
		return $this;
	}

	private function getAccountManagers()
	{
		$roleRepo = $this->em->getRepository(Role::getClassName());
		$userRepo = $this->em->getRepository(User::getClassName());

		$role = $roleRepo->findOneByName(Role::ADMIN);
		$users = $userRepo->findPairsByRoleId($role->id, 'mail');

		return $users;
	}

	private function getAccountManager($id)
	{
		$userRepo = $this->em->getRepository(User::getClassName());
		return $userRepo->find($id ? $id : $this->settings->modules->jobs->defaultAccountManagerId);
	}

	private function getTags($tagType)
	{
		$tags = [];
		foreach ($this->job->tags as $tagJob) {
			if ($tagJob->type == $tagType) {
				$tags[] = $tagJob->tag->name;
			}
		}
		return implode(',', $tags);
	}

	private function createTagIfNotExits($tagName, $tagType)
	{
		foreach ($this->job->tags as $tagJob) {
			if ($tagJob->tag == $tagName && $tagJob->type == $tagType) {
				return $tagJob;
			}
		}
		$tagRepo = $this->em->getRepository(Tag::getClassName());
		$newTag = new Tag($tagName);
		$tagRepo->save($newTag);
		$newTagJob = new TagJob();
		$newTagJob->job = $this->job;
		$newTagJob->tag = $newTag;
		$newTagJob->type = $tagType;
		return $newTagJob;
	}

	// </editor-fold>
}

interface IBasicInfoFactory
{

	/** @return BasicInfo */
	function create();
}
