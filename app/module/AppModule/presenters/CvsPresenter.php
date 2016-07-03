<?php

namespace App\AppModule\Presenters;

use App\Components\Candidate\ICandidateGalleryViewFactory;
use App\Components\Cv\ICvDataViewFactory;
use App\Components\Cv\ISkillsFilterFactory;
use App\Components\Grids\Cv\CvsGrid;
use App\Components\Grids\Cv\ICvsGridFactory;
use App\Components\Job\ISkillsControlFactory;
use App\Components\Job\SkillsControl;
use App\Model\Entity\Cv;
use App\Model\Entity\Job;
use App\TaggedString;
use Kdyby\Doctrine\EntityDao;
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
}
