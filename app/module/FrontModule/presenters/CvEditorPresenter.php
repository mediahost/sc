<?php

namespace App\FrontModule\Presenters;

use App\Components\Cv\AdditionalControl;
use App\Components\Cv\BasicInfoControl;
use App\Components\Cv\EducationsControl;
use App\Components\Cv\EmploymentControl;
use App\Components\Cv\ExperienceControl;
use App\Components\Cv\IAdditionalControlFactory;
use App\Components\Cv\IBasicInfoControlFactory;
use App\Components\Cv\IEducationsControlFactory;
use App\Components\Cv\IEmploymentControlFactory;
use App\Components\Cv\IExperienceControlFactory;
use App\Components\Cv\ILanguageControlFactory;
use App\Components\Cv\ILivePreviewControlFactory;
use App\Components\Cv\IObjectiveControlFactory;
use App\Components\Cv\IPersonalControlFactory;
use App\Components\Cv\ISkillsControlFactory;
use App\Components\Cv\ISummaryControlFactory;
use App\Components\Cv\IWorksControlFactory;
use App\Components\Cv\LanguageControl;
use App\Components\Cv\LivePreviewControl;
use App\Components\Cv\ObjectiveControl;
use App\Components\Cv\PersonalControl;
use App\Components\Cv\SkillsControl;
use App\Components\Cv\SummaryControl;
use App\Components\Cv\WorksControl;
use App\Model\Entity\Cv;
use App\Model\Facade\CvFacade;
use App\TaggedString;
use Exception;

class CvEditorPresenter extends BasePresenter
{

	/** @persistent int */
	public $id = NULL;

	// <editor-fold desc="inject">

	/** @var CvFacade @inject */
	public $cvFacade;

	/** @var IAdditionalControlFactory @inject */
	public $iAdditionalControlFactory;

	/** @var IEducationsControlFactory @inject */
	public $iEducationsControlFactory;

	/** @var IEmploymentControlFactory @inject */
	public $iEmploymentControlFactory;

	/** @var IExperienceControlFactory @inject */
	public $iExperienceControlFactory;

	/** @var ILanguageControlFactory @inject */
	public $iLanguageControlFactory;

	/** @var IObjectiveControlFactory @inject */
	public $iObjectiveControlFactory;

	/** @var IPersonalControlFactory @inject */
	public $iPersonalControlFactory;

	/** @var ISkillsControlFactory @inject */
	public $iSkillsControlFactory;

	/** @var IBasicInfoControlFactory @inject */
	public $iSettingsControlFactory;

	/** @var ISummaryControlFactory @inject */
	public $iSummaryControlFactory;

	/** @var IWorksControlFactory @inject */
	public $iWorksControlFactory;

	/** @var ILivePreviewControlFactory @inject */
	public $iLivePreviewControlFactory;

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var Cv */
	private $cv;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		if (array_key_exists('id', $this->params)) {
			$this->getCv($this->params['id']);
			if (!$this->id && $this->cv) {
				$this->id = $this->cv->id;
			}
		}
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->cv = $this->cv;
		$this->changeLastStep($this->action);
	}
	
	private function changeLastStep($action)
	{
		$allowedActions = $this->getAllowedActions();
		if ($searched = array_search($action, $allowedActions)) {
			$this->cv->lastStep = $searched;
			$cvRepo = $this->em->getRepository(Cv::getClassName());
			$cvRepo->save($this->cv);
		}
	}

	private function getCv($id)
	{
		try {
			$this->setCv($id);
		} catch (CvEditorPresenterException $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('Dashboard:');
		}
		return $this->cv;
	}

	private function setCv($id)
	{
		if ($this->cv) {
			return $this;
		}
		$candidate = $this->user->identity->candidate;

		if ($id) {
			$cvDao = $this->em->getDao(Cv::getClassName());
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
			throw new CvEditorPresenterException('Requested CV wasn\'t found.');
		}
		return $this;
	}

	private function getAllowedActions()
	{
		return [
			1 => 'works',
			'additional',
			'educations',
			'employment',
			'experience',
			'language',
			'objective',
			'personal',
			'skills',
			'settings',
			'summary',
		];
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('default')
	 */
	public function actionDefault($id = NULL)
	{
		$allowedActions = $this->getAllowedActions();
		$lastStep = 1;
		if (isset($allowedActions[$this->cv->lastStep])) {
			$lastStep = $this->cv->lastStep;
		}
		$this->redirect($allowedActions[$lastStep]);
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('additional')
	 */
	public function actionAdditional($id = NULL)
	{
		
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('educations')
	 */
	public function actionEducations($id = NULL)
	{
		
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('employment')
	 */
	public function actionEmployment($id = NULL)
	{
		
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('experience')
	 */
	public function actionExperience($id = NULL)
	{
		
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('language')
	 */
	public function actionLanguage($id = NULL)
	{
		
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('objective')
	 */
	public function actionObjective($id = NULL)
	{
		
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('personal')
	 */
	public function actionPersonal($id = NULL)
	{
		
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('skills')
	 */
	public function actionSkills($id = NULL)
	{
		
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('settings')
	 */
	public function actionSettings($id = NULL, $showTips = FALSE)
	{
		$this->template->showWalkThrough = $showTips;
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('summary')
	 */
	public function actionSummary($id = NULL)
	{
		
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('works')
	 */
	public function actionWorks($id = NULL)
	{
		
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('jobs')
	 */
	public function actionJobs($id = NULL)
	{
		$this->template->matchedJobs = $this->cvFacade->findJobs($this->cv);
	}

	// <editor-fold defaultstate="collapsed" desc="forms">

	/** @return AdditionalControl */
	public function createComponentAdditionalForm()
	{
		$control = $this->iAdditionalControlFactory->create();
		$control->setCv($this->cv);
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	/** @return EducationsControl */
	public function createComponentEducationsForm()
	{
		$control = $this->iEducationsControlFactory->create();
		$control->setCv($this->cv);
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	/** @return EmploymentControl */
	public function createComponentEmploymentForm()
	{
		$control = $this->iEmploymentControlFactory->create();
		$control->setCv($this->cv);
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	/** @return ExperienceControl */
	public function createComponentExperienceForm()
	{
		$control = $this->iExperienceControlFactory->create();
		$control->setCv($this->cv);
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	/** @return LanguageControl */
	public function createComponentLanguageForm()
	{
		$control = $this->iLanguageControlFactory->create();
		$control->setCv($this->cv);
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	/** @return ObjectiveControl */
	public function createComponentObjectiveForm()
	{
		$control = $this->iObjectiveControlFactory->create();
		$control->setCv($this->cv);
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	/** @return PersonalControl */
	public function createComponentPersonalForm()
	{
		$control = $this->iPersonalControlFactory->create();
		$control->setCv($this->cv);
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	/** @return BasicInfoControl */
	public function createComponentSettingsForm()
	{
		$control = $this->iSettingsControlFactory->create();
		$control->setAjax(TRUE, TRUE);
		$control->setCv($this->cv);
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	/** @return SkillsControl */
	public function createComponentSkillsForm()
	{
		$control = $this->iSkillsControlFactory->create();
		$control->setCv($this->cv);
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	/** @return SummaryControl */
	public function createComponentSummaryForm()
	{
		$control = $this->iSummaryControlFactory->create();
		$control->setCv($this->cv);
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	/** @return WorksControl */
	public function createComponentWorksForm()
	{
		$control = $this->iWorksControlFactory->create();
		$control->setCv($this->cv);
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	public function standardOnAfterSave(Cv $saved)
	{
		$message = new TaggedString('Cv \'%s\' was successfully saved.', (string) $saved);
		$this->flashMessage($message, 'success');
		if ($this->isAjax()) {
			$this['cvPreview']->redrawControl();
			$this->redrawControl();
		} else {
			$this->redirect('this');
		}
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="preview">

	/** @return LivePreviewControl */
	public function createComponentCvPreview()
	{
		$control = $this->iLivePreviewControlFactory->create();
		$control->setCv($this->cv);
		$control->setScale(0.6);
		return $control;
	}

	// </editor-fold>
}

class CvEditorPresenterException extends Exception
{
	
}
