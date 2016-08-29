<?php

namespace App\AppModule\Presenters;

use App\Components\AfterRegistration\ICompleteCandidatePreviewFactory;
use App\Components\Candidate\ICandidateGalleryViewFactory;
use App\Components\Cv;
use App\Components\Job\Skills;
use App\Model\Entity;
use App\Model\Facade\UserFacade;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;

class CvsPresenter extends BasePresenter
{
	// <editor-fold desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var ICandidateGalleryViewFactory @inject */
	public $candidateGalleryViewFactory;

	/** @var Cv\ICvDataViewFactory @inject */
	public $cvDataViewFactory;

	/** @var Cv\ISkillsFilterFactory @inject */
	public $iSkillFilterFactory;

	/** @var Cv\ISkillsFactory @inject */
	public $iSkillsFactory;

	/** @var Cv\ILivePreviewFactory @inject */
	public $iLivePreviewFactory;

	/** @var ICompleteCandidatePreviewFactory @inject */
	public $completeCandidatePreview;

	/** @var UserFacade @inject */
	public $userFacade;

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var EntityRepository */
	private $cvRepo;

	/** @var EntityRepository */
	private $jobRepo;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->cvRepo = $this->em->getRepository(Entity\Cv::getClassName());
		$this->jobRepo = $this->em->getRepository(Entity\Job::getClassName());
	}

	// <editor-fold desc="actions & renderers">

	/**
	 * @secured
	 * @resource('cvs')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		$this->template->pageTitle = $this->translator->translate('Candidates');
	}

	public function actionCandidates()
	{
		$this['candidateGalleryView']->resetFilter();
		$this->redirect('default');
	}

	public function actionCvDetail($userId)
	{
		$user = $this->userFacade->findById($userId);
		$this['completeCandidatePreview']->setUserEntity($user);
		$this['skillsForm']->setCv($user->candidate->getDefaultCv());
		$this->redrawControl('cvDetail');
	}

	/**
	 * @secured
	 * @resource('cvs')
	 * @privilege('delete')
	 */
	public function actionDelete($id)
	{
		$message = $this->translator->translate('Not implemented yet.');
		$this->flashMessage($message, 'warning');
		$this->redirect('default');
	}

	public function createComponentCvDataView()
	{
		$control = $this->cvDataViewFactory->create();
		return $control;
	}

	/** @return Skills */
	public function createComponentSkillFilter()
	{
		$control = $this->iSkillFilterFactory->create();
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSend = function (array $skillRequests) {
			$this['cvDataView']->setSkillRequests($skillRequests);
			$this->redrawControl();
		};
		return $control;
	}

	public function createComponentCandidateGalleryView()
	{
		$control = $this->candidateGalleryViewFactory->create();
		return $control;
	}

	public function createComponentCvPreview()
	{
		$control = $this->iLivePreviewFactory->create();
		$control->setScale(0.8, 0.8, 1);
		return $control;
	}

	public function createComponentCompleteCandidatePreview()
	{
		$control = $this->completeCandidatePreview->create();
		return $control;
	}

	public function createComponentSkillsForm()
	{
		$control = $this->iSkillsFactory->create();
		$control->setTemplateFile('overview');
		$control->onlyFilledSkills = true;
		$control->setAjax(TRUE, TRUE);
		return $control;
	}
}
