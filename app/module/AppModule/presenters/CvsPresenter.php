<?php

namespace App\AppModule\Presenters;

use App\Components\Candidate\ICandidateGalleryViewFactory;
use App\Components\AfterRegistration\ICompleteCandidatePreviewFactory;
use App\Components\Cv\ILivePreviewControlFactory;
use App\Components\Cv\ICvDataViewFactory;
use App\Components\Cv\ISkillsFilterFactory;
use App\Components\Cv\ISkillsControlFactory;
use App\Components\Job\SkillsControl;
use App\Model\Facade\UserFacade;
use App\Model\Entity\Cv;
use App\Model\Entity\Job;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;

class CvsPresenter extends BasePresenter
{
	// <editor-fold desc="injects">

	/** @var EntityManager @inject */
	public $em;
    
    /** @var ICandidateGalleryViewFactory @inject */
	public $candidateGalleryViewFactory;

	/** @var ICvDataViewFactory @inject */
	public $cvDataViewFactory;

	/** @var ISkillsFilterFactory @inject */
	public $iSkillFilterFactory;
    
    /** @var ISkillsControlFactory @inject */
	public $iSkillsControlFactory;
    
    /** @var ILivePreviewControlFactory @inject */
	public $iLivePreviewControlFactory;
    
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
		$this->cvRepo = $this->em->getRepository(Cv::getClassName());
		$this->jobRepo = $this->em->getRepository(Job::getClassName());
	}

	// <editor-fold desc="actions & renderers">

	/**
	 * @secured
	 * @resource('cvs')
	 * @privilege('default')
	 */
	public function actionDefault($jobId = NULL, $filter = FALSE)
	{
        $this->template->pageTitle = $this->translator->translate('Candidates');
		if ($jobId) {
			$job = $this->jobRepo->find($jobId);
			if ($job) {
				$this['cvDataView']->setJob($job);
                $msg = 'Suggested candidates for job ' . $job->name;
                $this->template->pageTitle = $this->translator->translate($msg);
			}
		}
		$this->getTemplate()->showFilter = $filter;
	}
    
    public function actionCvDetail($userId) {
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
		$this->flashMessage('Not implemented yet.', 'warning');
		$this->redirect('default');
	}

	public function createComponentCvDataView()
	{
		$control = $this->cvDataViewFactory->create();
		return $control;
	}

	/** @return SkillsControl */
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
    
    public function createComponentCandidateGalleryView() {
        $control = $this->candidateGalleryViewFactory->create();
        return $control;
    }
    
    public function createComponentCvPreview()
	{
		$control = $this->iLivePreviewControlFactory->create();
		$control->setScale(0.8, 0.8, 1);
		return $control;
	}
    
    public function createComponentCompleteCandidatePreview() {
        $control = $this->completeCandidatePreview->create();
        return $control;
    }
    
    public function createComponentSkillsForm()
	{
		$control = $this->iSkillsControlFactory->create();
		$control->setTemplateFile('overview');
		$control->onlyFilledSkills = true;
		$control->setAjax(TRUE, TRUE);
		return $control;
	}
}
