<?php

namespace App\AppModule\Presenters;

use App\Components\Cv;
use App\Components\User\CareerDocs;
use App\Components\User\ICareerDocsFactory;
use App\Model\Entity;
use App\Model\Facade\CvFacade;
use Doctrine\ORM\EntityNotFoundException;
use Exception;

class CvEditorPresenter extends BasePresenter
{
	/** @persistent int */
	public $id = NULL;

	/** @var CvFacade @inject */
	public $cvFacade;

	/** @var Cv\ISkillsFactory @inject */
	public $iSkillsFactory;

	/** @var Cv\IBasicInfoFactory @inject */
	public $iBasicInfoFactory;

	/** @var Cv\ILivePreviewFactory @inject */
	public $iLivePreviewFactory;

	/** @var Cv\ISendEmailFactory @inject */
	public $iSendEmailFactory;

	/** @var Cv\IWorksFactory @inject */
	public $iWorksFactory;

	/** @var Cv\IEducationsFactory @inject */
	public $iEducationsFactory;

	/** @var Cv\IExperienceFactory @inject */
	public $iExperienceFactory;

	/** @var Cv\IPersonalFactory @inject */
	public $iPersonalFactory;

	/** @var Cv\ILanguageFactory @inject */
	public $iLanguageFactory;

	/** @var Cv\IOtherLanguageFactory @inject */
	public $iOtherLanguageFactory;

	/** @var Cv\IObjectiveFactory @inject */
	public $iObjectiveFactory;

	/** @var Cv\IEmploymentFactory @inject */
	public $iEmploymentFactory;

	/** @var Cv\ISummaryFactory @inject */
	public $iSummaryFactory;

	/** @var Cv\IAdditionalFactory @inject */
	public $iAdditionalFactory;

	/** @var ICareerDocsFactory @inject */
	public $iCareerDocsFactory;

	/** @var Entity\Cv */
	private $cv;

	protected function startup()
	{
		parent::startup();
		$this->hideRightSidebar();
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->cv = $this->cv;
	}

	private function getCv($id)
	{
		try {
			$this->setCv($id);
		} catch (EntityNotFoundException $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('Dashboard:');
		}
		return $this->cv;
	}

	private function setCv($id)
	{
		if ($this->cv) {
			return;
		}
		$candidate = $this->user->identity->getCandidate();

		if ($id) {
			$cvDao = $this->em->getDao(Entity\Cv::getClassName());
			$findedCv = $cvDao->find($id);
			$isOwnCv = $candidate && $findedCv->candidate->id === $candidate->id;
			$canEditForeignCv = $findedCv && $this->user->isAllowed('cvEditor', 'editForeign');
			if ($isOwnCv || $canEditForeignCv) {
				$this->cv = $findedCv;
			}
		} else if ($candidate) { // pro kandidáta načti defaultní
			$this->cv = $this->cvFacade->getDefaultCvOrCreate($candidate);
		}

		if (!$this->cv) {
			throw new EntityNotFoundException('Requested CV wasn\'t found.');
		}
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('default')
	 */
	public function actionDefault($id = NULL, $withPreview = TRUE)
	{
		$this->getCv($id);
		$this->template->showPreview = $withPreview;
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('skills')
	 */
	public function actionSkills($id = NULL)
	{
		$this->getCv($id);
		$this['skillsForm']->setTemplateFile('ItSkills');
	}

	public function actionCareerDocs($userId = NULL)
	{
		if ($userId && $this->user->isAllowed('cvEditor', 'editForeign')) {
			$candidate = $this->userFacade->findById($userId)->candidate;
		} else {
			$candidate = $this->user->identity->candidate;
		}
		$this['careerDocs']->setCandidate($candidate);
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('jobs')
	 */
	public function actionJobs($id = NULL)
	{
		$this->getCv($id);
		$this->template->matchedJobs = $this->cvFacade->findJobs($this->cv);
	}

	public function afterCvSave()
	{
		$message = $this->translator->translate('Cv \'%cv%\' was successfully saved.', ['cv' => (string)$this->cv]);
		$this->flashMessage($message, 'success');

		if ($this->isAjax()) {
			$this->payload->reloadPreview = true;
		} else {
			$this->redirect('this');
		}
	}

	/** @return BasicInfo */
	public function createComponentBasicInfoForm()
	{
		$control = $this->iBasicInfoFactory->create();
		$control->setAjax(TRUE, TRUE);
		$control->setCv($this->cv);
		$control->onAfterSave = $this->afterCvSave;
		return $control;
	}

	/** @return Works */
	public function createComponentWorksForm()
	{
		$control = $this->iWorksFactory->create();
		$control->setCv($this->cv);
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterCvSave;
		return $control;
	}

	/** @return Educations */
	public function createComponentEducationsForm()
	{
		$control = $this->iEducationsFactory->create();
		$control->setCv($this->cv);
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterCvSave;
		return $control;
	}

	/** @return Skills */
	public function createComponentSkillsForm()
	{
		$control = $this->iSkillsFactory->create();
		$control->setCv($this->cv);
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterCvSave;
		return $control;
	}

	/** @return Experience */
	public function createComponentExperienceForm()
	{
		$control = $this->iExperienceFactory->create();
		$control->setCv($this->cv);
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterCvSave;
		return $control;
	}

	/** @return Personal */
	public function createComponentPersonalForm()
	{
		$control = $this->iPersonalFactory->create();
		$control->setCv($this->cv);
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterCvSave;
		return $control;
	}

	/** @return Language */
	public function createComponentLanguageForm()
	{
		$control = $this->iLanguageFactory->create();
		$control->setCv($this->cv);
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterCvSave;
		return $control;
	}

	/** @return OtherLanguage */
	public function createComponentOtherLanguageForm()
	{
		$control = $this->iOtherLanguageFactory->create();
		$control->setCv($this->cv);
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterCvSave;
		return $control;
	}

	/** @return Objective */
	public function createComponentObjectiveForm()
	{
		$control = $this->iObjectiveFactory->create();
		$control->setCv($this->cv);
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterCvSave;
		return $control;
	}

	/** @return Employment */
	public function createComponentEmploymentForm()
	{
		$control = $this->iEmploymentFactory->create();
		$control->setCv($this->cv);
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterCvSave;
		return $control;
	}

	/** @return Summary */
	public function createComponentSummaryForm()
	{
		$control = $this->iSummaryFactory->create();
		$control->setCv($this->cv);
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterCvSave;
		return $control;
	}

	/** @return Additional */
	public function createComponentAdditionalForm()
	{
		$control = $this->iAdditionalFactory->create();
		$control->setCv($this->cv);
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = $this->afterCvSave;
		return $control;
	}

	/** @return LivePreview */
	public function createComponentCvPreview()
	{
		$control = $this->iLivePreviewFactory->create();
		$control->setScale(0.8, 0.8, 1);
		$control->setCv($this->cv);
		return $control;
	}

	/** @return SendEmail */
	public function createComponentSendEmail()
	{
		$control = $this->iSendEmailFactory->create();
		$control->setCv($this->cv);
		return $control;
	}

	/** @return CareerDocs */
	public function createComponentCareerDocs()
	{
		$control = $this->iCareerDocsFactory->create();
		$control->onAfterSave[] = function () {
			$this->redirect('this');
		};
		return $control;
	}
}
