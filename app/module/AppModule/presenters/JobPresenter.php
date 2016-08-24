<?php

namespace App\AppModule\Presenters;

use App\ArrayUtils;
use App\Components\Candidate\ICandidateFilterFactory;
use App\Components\Candidate\ICandidatePreviewFactory;
use App\Components\Job\BasicInfoControl;
use App\Components\Job\IBasicInfoControlFactory;
use App\Components\Job\IDescriptionsControlFactory;
use App\Components\Job\INotesControlFactory;
use App\Components\Job\IOffersControlFactory;
use App\Components\Job\IQuestionsControlFactory;
use App\Components\Job\ISkillsControlFactory;
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

	/** @var IBasicInfoControlFactory @inject */
	public $iJobBasicInfoControlFactory;

	/** @var IOffersControlFactory @inject */
	public $iIOffersControlFactory;

	/** @var IDescriptionsControlFactory @inject */
	public $iDescriptionsControlFactory;

	/** @var ISkillsControlFactory @inject */
	public $iJobSkillsControlFactory;

	/** @var IQuestionsControlFactory @inject */
	public $iQuestionsControlFactory;

	/** @var ICandidateFilterFactory @inject */
	public $candidateFilterFactory;

	/** @var ICandidatePreviewFactory @inject */
	public $candidatePreviewFactory;

	/** @var INotesControlFactory @inject */
	public $notesControlFactory;

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var EntityDao */
	private $jobDao;

	/** @var EntityDao */
	private $companyDao;


	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->jobDao = $this->em->getDao(Job::getClassName());
		$this->companyDao = $this->em->getDao(Company::getClassName());
	}

	// <editor-fold desc="actions & renderers">

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('view')
	 */
	public function actionView($id)
	{
		$job = $this->jobDao->find($id);
		if ($job) {
			$this->template->job = $job;
			$this->template->matchedCvs = $this->jobFacade->findCvs($job);
		} else {
			$message = 'Finded job isn\'t exists.';
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
		$company = $this->companyDao->find($companyId);
		if ($company) {
			$this->job = new Job;
			$this->job->company = $company;
			$this['jobInfoForm']->setJob($this->job);
			$this['jobOffersForm']->setJob($this->job);
			$this['jobDescriptionsForm']->setJob($this->job);
			$this['jobSkillsForm']->setJob($this->job);
			$this['jobQuestionsForm']->setJob($this->job);
			$this['notesControl']->setJob($this->job);
		} else {
			$message = 'Finded company isn\'t exists.';
			$this->flashMessage($message, 'danger');
			$this->redirect('Dashboard:');
		}
		$this->setView('edit');
	}

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('edit')
	 */
	public function actionEdit($id)
	{
		$this->job = $this->jobDao->find($id);
		if ($this->job) {
			$this['jobInfoForm']->setJob($this->job);
			$this['jobOffersForm']->setJob($this->job);
			$this['jobDescriptionsForm']->setJob($this->job);
			$this['jobSkillsForm']->setJob($this->job);
			$this['jobQuestionsForm']->setJob($this->job);
			$this['notesControl']->setJob($this->job);
		} else {
			$message = 'Finded job isn\'t exists.';
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
	// <editor-fold desc="edit/delete priviledges">
	// </editor-fold>
	// <editor-fold desc="forms">

	/** @return BasicInfoControl */
	public function createComponentJobInfoForm()
	{
		$control = $this->iJobBasicInfoControlFactory->create();
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = function ($job) {
			$this->afterJobSave($job);
		};
		return $control;
	}

	/** @return OffersControl */
	public function createComponentJobOffersForm()
	{
		$control = $this->iIOffersControlFactory->create();
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterJobSave;
		return $control;
	}

	/** @return DescriptionsControl */
	public function createComponentJobDescriptionsForm()
	{
		$control = $this->iDescriptionsControlFactory->create();
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterJobSave;
		return $control;
	}

	/** @return SkillsControl */
	public function createComponentJobSkillsForm()
	{
		$control = $this->iJobSkillsControlFactory->create();
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterJobSave;
		return $control;
	}

	/** @return QuestionsControl */
	public function createComponentJobQuestionsForm()
	{
		$control = $this->iQuestionsControlFactory->create();
		$control->setAjax(TRUE, TRUE);
		return $control;
	}

	/** @return CandidateFilter */
	public function createComponentCandidateFilter()
	{
		$control = $this->candidateFilterFactory->create();
		$control->setAjax(TRUE, TRUE);
		return $control;
	}

	/** @return NotesControl */
	public function createComponentNotesControl()
	{
		$control = $this->notesControlFactory->create();
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = function ($job) {
			$this->afterJobSave($job);
		};
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
