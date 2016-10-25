<?php

namespace App\AppModule\Presenters;

use App\ArrayUtils;
use App\Components\Candidate\ICandidateFilterFactory;
use App\Components\Candidate\ICandidatePreviewFactory;
use App\Components\Job\BasicInfo;
use App\Components\Job\Descriptions;
use App\Components\Job\IAccountAdminFactory;
use App\Components\Job\IBasicInfoFactory;
use App\Components\Job\IDescriptionsFactory;
use App\Components\Job\INotesFactory;
use App\Components\Job\IOffersFactory;
use App\Components\Job\IQuestionsFactory;
use App\Components\Job\ISkillsFactory;
use App\Model\Entity\Company;
use App\Model\Entity\Cv;
use App\Model\Entity\Job;
use App\Model\Facade\JobFacade;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Multiplier;

class JobPresenter extends BasePresenter
{

	/** @persistent int */
	public $companyId;

	/** @var Job */
	private $job;

	// <editor-fold desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var JobFacade @inject */
	public $jobFacade;

	/** @var IBasicInfoFactory @inject */
	public $iJobBasicInfoFactory;

	/** @var IOffersFactory @inject */
	public $iIOffersFactory;

	/** @var IDescriptionsFactory @inject */
	public $iDescriptionsFactory;

	/** @var ISkillsFactory @inject */
	public $iJobSkillsFactory;

	/** @var IQuestionsFactory @inject */
	public $iQuestionsFactory;

	/** @var ICandidateFilterFactory @inject */
	public $candidateFactory;

	/** @var ICandidatePreviewFactory @inject */
	public $candidatePreviewFactory;

	/** @var INotesFactory @inject */
	public $notesFactory;

	/** @var IAccountAdminFactory @inject */
	public $accountAdminFactory;

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var EntityDao */
	private $jobRepository;

	/** @var EntityDao */
	private $companyRepository;


	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->jobRepository = $this->em->getRepository(Job::getClassName());
		$this->companyRepository = $this->em->getRepository(Company::getClassName());
	}

	// <editor-fold desc="actions & renderers">

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('view')
	 */
	public function actionView($id)
	{
		$job = $this->jobRepository->find($id);
		if ($job) {
			$this->template->job = $job;
		} else {
			$message = $this->translator->translate('Finded job isn\'t exists.');
			$this->flashMessage($message, 'danger');
			$this->redirect('Dashboard:');
		}
	}

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('add')
	 */
	public function actionAdd($companyId)
	{
		$company = $this->companyRepository->find($companyId);
		if ($company) {
			$this->job = new Job;
			$this->job->company = $company;
			$this['jobInfoForm']->setJob($this->job);
		} else {
			$message = $this->translator->translate('Finded company isn\'t exists.');
			$this->flashMessage($message, 'danger');
			$this->redirect('Dashboard:');
		}
	}

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('edit')
	 */
	public function actionEdit($id)
	{
		$this->job = $this->jobRepository->find($id);
		if ($this->job) {
			$this['jobInfoForm']->setJob($this->job);
			$this['jobOffersForm']->setJob($this->job);
			$this['jobDescriptionsForm']->setJob($this->job);
			$this['jobSkillsForm']->setJob($this->job);
			$this['jobQuestionsForm']->setJob($this->job);
			$this['notes']->setJob($this->job);
			$this['accountAdmin']->setJob($this->job);
		} else {
			$message = $this->translator->translate('Finded job isn\'t exists.');
			$this->flashMessage($message, 'danger');
			$this->redirect('Dashboard:');
		}
	}

	public function renderEdit()
	{
		$cvRepo = $this->em->getRepository(Cv::getClassName());
		$this->template->job = $this->job;
	}

	public function actionCvDetail($userId)
	{

	}

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('delete')
	 */
	public function actionDelete($id)
	{
		$this->jobFacade->delete($id);
		$this->redirect('Jobs:showAll');
	}

	public function afterJobSave(Job $job)
	{
		$message = $this->translator->translate('Job \'%job%\' was successfully saved.', ['job' => (string)$job]);
		$this->flashMessage($message, 'success');
	}

	// </editor-fold>
	// <editor-fold desc="edit/delete privileges">
	// </editor-fold>
	// <editor-fold desc="forms">

	/** @return BasicInfo */
	public function createComponentJobInfoForm()
	{
		$control = $this->iJobBasicInfoFactory->create();
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = function($job) {
			$message = $this->translator->translate('Job \'%job%\' was successfully saved.', ['job' => (string)$job]);
			$this->flashMessage($message, 'success');
			$this->forward('edit', $job->id);
		};
		return $control;
	}

	/** @return Offers */
	public function createComponentJobOffersForm()
	{
		$control = $this->iIOffersFactory->create();
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterJobSave;
		return $control;
	}

	/** @return Descriptions */
	public function createComponentJobDescriptionsForm()
	{
		$control = $this->iDescriptionsFactory->create();
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterJobSave;
		return $control;
	}

	/** @return Skills */
	public function createComponentJobSkillsForm()
	{
		$control = $this->iJobSkillsFactory->create();
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterJobSave;
		return $control;
	}

	/** @return Questions */
	public function createComponentJobQuestionsForm()
	{
		$control = $this->iQuestionsFactory->create();
		$control->setAjax(TRUE, TRUE);
		return $control;
	}

	/** @return CandidateFilter */
	public function createComponentCandidateFilter()
	{
		$control = $this->candidateFactory->create();
		$control->setAjax(TRUE, TRUE);
		return $control;
	}

	/** @return Notes */
	public function createComponentNotes()
	{
		$control = $this->notesFactory->create();
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterJobSave;
		return $control;
	}

	/** @return \App\Components\Job\AccountAdmin */
	public function createComponentAccountAdmin()
	{
		$control = $this->accountAdminFactory->create();
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterJobSave;
		return $control;
	}

	public function createComponentCandidatePreview()
	{
		return new Multiplier(function ($cvId) {
			$cv = ArrayUtils::searchByProperty($this->job->cvs, 'id', $cvId);
			$control = $this->candidatePreviewFactory->create();
			$control->setCv($cv);
			return $control;
		});
	}

	// </editor-fold>
}
