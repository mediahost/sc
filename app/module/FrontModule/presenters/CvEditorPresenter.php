<?php

namespace App\FrontModule\Presenters;

use App\Components\Cv;
use App\Model\Entity;
use App\Model\Facade\CvFacade;
use Exception;

class CvEditorPresenter extends BasePresenter
{

	/** @persistent int */
	public $id = NULL;

	// <editor-fold desc="inject">

	/** @var CvFacade @inject */
	public $cvFacade;

	/** @var Cv\IAdditionalFactory @inject */
	public $iAdditionalFactory;

	/** @var Cv\IEducationsFactory @inject */
	public $iEducationsFactory;

	/** @var Cv\IEmploymentFactory @inject */
	public $iEmploymentFactory;

	/** @var Cv\IExperienceFactory @inject */
	public $iExperienceFactory;

	/** @var Cv\ILanguageFactory @inject */
	public $iLanguageFactory;

	/** @var Cv\IOtherLanguageFactory @inject */
	public $iOtherLanguageFactory;

	/** @var Cv\IObjectiveFactory @inject */
	public $iObjectiveFactory;

	/** @var Cv\IPersonalFactory @inject */
	public $iPersonalFactory;

	/** @var Cv\ISkillsFilterFactory @inject */
	public $iSkillsFactory;

	/** @var Cv\IBasicInfoFactory @inject */
	public $iSettingsFactory;

	/** @var Cv\ISummaryFactory @inject */
	public $iSummaryFactory;

	/** @var Cv\IWorksFactory @inject */
	public $iWorksFactory;

	/** @var Cv\ILivePreviewFactory @inject */
	public $iLivePreviewFactory;

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var Entity\Cv */
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

	private function saveCv()
	{
		$cvRepo = $this->em->getRepository(Entity\Cv::getClassName());
		$cvRepo->save($this->cv);
		return $this;
	}

	private function changeLastStep($action)
	{
		$allowedActions = $this->getAllowedActions();
		if ($searched = array_search($action, $allowedActions)) {
			$this->cv->lastStep = $searched;
			$this->saveCv();
		}
	}

	private function getCv($id)
	{
		try {
			$this->setCv($id);
		} catch (CvEditorPresenterException $ex) {
			$message = $this->translator->translate($ex->getMessage());
			$this->flashMessage($message, 'danger');
			$this->redirect('Homepage:');
		}
		return $this->cv;
	}

	private function setCv($id)
	{
		if ($this->cv) {
			return $this;
		}
		$user = $this->user->identity;
		$candidate = $user->person->candidate;

		if ($id) {
			$cvDao = $this->em->getDao(Entity\Cv::getClassName());
			$findedCv = $cvDao->find($id);
			$isOwnCv = $candidate && $findedCv->candidate->id === $candidate->id;
			$canEditForeignCv = $findedCv && $this->user->isAllowed('cvEditor', 'editForeign');
			if ($isOwnCv || $canEditForeignCv) {
				$this->cv = $findedCv;
			}
		} else if ($candidate) {
			$this->cv = $candidate->cv;
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
	 * @privilege('editEducation')
	 */
	public function actionEditEducation($id = NULL, $eduId = NULL)
	{
		$eduDao = $this->em->getDao(Education::getClassName());
		$edu = $eduDao->find($eduId);
		$this['educationsForm']->setEducation($edu);
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('deleteEducation')
	 */
	public function handleDeleteEducation($eduId = NULL)
	{
		if ($this->cv->existsEducationId($eduId)) {
			$eduDao = $this->em->getDao(Education::getClassName());
			$edu = $eduDao->find($eduId);
			$eduDao->delete($edu);
		}
		$this->redirect('this');
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
	 * @privilege('editExperience')
	 */
	public function actionEditExperience($id = NULL, $expId = NULL)
	{
		$expDao = $this->em->getDao(Entity\Work::getClassName());
		$exp = $expDao->find($expId);
		$this['experienceForm']->setExperience($exp);
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('deleteExperience')
	 */
	public function handleDeleteExperience($expId = NULL)
	{
		if ($this->cv->existsExperienceId($expId)) {
			$expDao = $this->em->getDao(Entity\Work::getClassName());
			$exp = $expDao->find($expId);
			$expDao->delete($exp);
		}
		$this->redirect('this');
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
	 * @privilege('editLanguage')
	 */
	public function actionEditLanguage($id = NULL, $langId = NULL)
	{
		$langDao = $this->em->getDao(Entity\Language::getClassName());
		$lang = $langDao->find($langId);
		$this['languageForm']->setLanguage($lang);
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('deleteLanguage')
	 */
	public function handleDeleteLanguage($langId = NULL)
	{
		if ($this->cv->existsLanguageId($langId)) {
			$langDao = $this->em->getDao(Entity\Language::getClassName());
			$lang = $langDao->find($langId);
			$langDao->delete($lang);
		}
		$this->redirect('this');
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
	 * @privilege('editWork')
	 */
	public function actionEditWork($id = NULL, $workId = NULL)
	{
		$workDao = $this->em->getDao(Entity\Work::getClassName());
		$work = $workDao->find($workId);
		$this['worksForm']->setWork($work);
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('deleteWork')
	 */
	public function handleDeleteWork($workId = NULL)
	{
		if ($this->cv->existsWorkId($workId)) {
			$workDao = $this->em->getDao(Entity\Work::getClassName());
			$work = $workDao->find($workId);
			$workDao->delete($work);
		}
		$this->redirect('this');
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

	// <editor-fold desc="forms">

	/** @return Cv\Additional */
	public function createComponentAdditionalForm()
	{
		$control = $this->iAdditionalFactory->create();
		$control->setCv($this->cv);
		$control->setAjax();
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	/** @return Cv\Educations */
	public function createComponentEducationsForm()
	{
		$control = $this->iEducationsFactory->create();
		$control->setCv($this->cv);
		$control->onAfterSave = function (Entity\Cv $saved) {
			$message = $this->translator->translate('Cv \'%cv%\' was successfully saved.', ['cv' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redirect('educations');
		};
		return $control;
	}

	/** @return Cv\Employment */
	public function createComponentEmploymentForm()
	{
		$control = $this->iEmploymentFactory->create();
		$control->setCv($this->cv);
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	/** @return Cv\Experience */
	public function createComponentExperienceForm()
	{
		$control = $this->iExperienceFactory->create();
		$control->setCv($this->cv);
		$control->onAfterSave = function (Entity\Cv $saved) {
			$message = $this->translator->translate('Cv \'%cv%\' was successfully saved.', ['cv' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redirect('experience');
		};
		return $control;
	}

	/** @return Cv\Language */
	public function createComponentMotherLanguageForm()
	{
		$control = $this->iLanguageFactory->create();
		$control->setCv($this->cv);
		$control->setAjax();
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	/** @return Cv\OtherLanguage */
	public function createComponentLanguageForm()
	{
		$control = $this->iOtherLanguageFactory->create();
		$control->setCv($this->cv);
		$control->onAfterSave = function (Entity\Cv $saved) {
			$message = $this->translator->translate('Cv \'%cv%\' was successfully saved.', ['cv' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redirect('language');
		};
		return $control;
	}

	/** @return Cv\Objective */
	public function createComponentObjectiveForm()
	{
		$control = $this->iObjectiveFactory->create();
		$control->setCv($this->cv);
		$control->setAjax();
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	/** @return Cv\Personal */
	public function createComponentPersonalForm()
	{
		$control = $this->iPersonalFactory->create();
		$control->setCv($this->cv);
		$control->setAjax();
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	/** @return Cv\BasicInfo */
	public function createComponentSettingsForm()
	{
		$control = $this->iSettingsFactory->create();
		$control->setAjax();
		$control->setCv($this->cv);
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	/** @return Cv\Skills */
	public function createComponentSkillsForm()
	{
		$control = $this->iSkillsFactory->create();
		$control->setCv($this->cv);
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	/** @return Cv\Summary */
	public function createComponentSummaryForm()
	{
		$control = $this->iSummaryFactory->create();
		$control->setCv($this->cv);
		$control->setAjax();
		$control->onAfterSave = $this->standardOnAfterSave;
		return $control;
	}

	/** @return Cv\Works */
	public function createComponentWorksForm()
	{
		$control = $this->iWorksFactory->create();
		$control->setCv($this->cv);
		$control->onAfterSave = function (Entity\Cv $saved) {
			$message = $this->translator->translate('Cv \'%cv%\' was successfully saved.', ['cv' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redirect('works');
		};
		return $control;
	}

	public function standardOnAfterSave(Entity\Cv $saved)
	{
		$message = $this->translator->translate('Cv \'%cv%\' was successfully saved.', ['cv' => (string)$saved]);
		$this->flashMessage($message, 'success');
		if ($this->isAjax()) {
			$this['cvPreview']->redrawControl();
			$this->redrawControl();
		} else {
			$this->redirect('this');
		}
	}

	// </editor-fold>
	// <editor-fold desc="preview">

	/** @return Cv\LivePreview */
	public function createComponentCvPreview()
	{
		$control = $this->iLivePreviewFactory->create();
		$control->setCv($this->cv);
		$control->setScale(0.6);
		return $control;
	}

	// </editor-fold>
}

class CvEditorPresenterException extends Exception
{

}
