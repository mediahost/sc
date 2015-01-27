<?php

namespace App\AppModule\Presenters;

use App\Components\Cv\ILivePreviewControlFactory;
use App\Components\Cv\ISkillKnowsControlFactory;
use App\Components\Cv\LivePreviewControl;
use App\Components\Cv\SkillKnowsControl;
use App\Model\Entity\Cv;
use App\Model\Entity\Skill;
use App\Model\Facade\CvFacade;
use App\TaggedString;

/**
 * 
 */
class CvEditorPresenter extends BasePresenter
{

	/** @persistent int */
	public $id = NULL;

	// <editor-fold defaultstate="collapsed" desc="inject">

	/** @var CvFacade @inject */
	public $cvFacade;

	/** @var ISkillKnowsControlFactory @inject */
	public $iSkillKnowsControlFactory;

	/** @var ILivePreviewControlFactory @inject */
	public $iLivePreviewControlFactory;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var Cv */
	private $cv;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->cv = $this->cv;
	}

	private function getCv()
	{
		if (!$this->cv) {
			$candidate = $this->user->identity->candidate;

			if ($this->id) {
				$cvDao = $this->em->getDao(Cv::getClassName());
				$findedCv = $cvDao->find($this->id);

				if (($findedCv && $candidate && $findedCv->candidate->id === $candidate->id) ||
						($findedCv && $this->user->isAllowed('cvEditor', 'editForeign'))) {
					$this->cv = $findedCv;
				}
			} else if ($candidate) { // pro kandidáta načti defaultní
				$this->cv = $this->cvFacade->getDefaultCv($this->user->identity->candidate);
			}
		}
		if (!$this->cv) {
			$this->flashMessage('Requested CV wasn\'t found.');
			$this->redirect('Dashboard:');
		}
		return $this->cv;
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('default')
	 */
	public function actionDefault($id = NULL, $withPreview = TRUE)
	{
		$this->getCv();
		$this->template->skills = $this->em->getDao(Skill::getClassName())->findAll();
		$this->template->showPreview = $withPreview;
	}

	/**
	 * @secured
	 * @resource('cvEditor')
	 * @privilege('skills')
	 */
	public function actionSkills($id = NULL)
	{
		$this->getCv();
	}

	// <editor-fold defaultstate="collapsed" desc="forms">

	/** @return SkillKnowsControl */
	public function createComponentSkillsForm()
	{
		$control = $this->iSkillKnowsControlFactory->create();
		$control->setCv($this->cv);
		$control->onAfterSave = function (Cv $saved) {
			$message = new TaggedString('Cv \'%s\' was successfully saved.', (string) $saved);
			$this->flashMessage($message, 'success');
			$this->redirect('this');
		};
		return $control;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="preview">

	/** @return LivePreviewControl */
	public function createComponentCvPreview()
	{
		$control = $this->iLivePreviewControlFactory->create();
		$control->setCv($this->cv);
		return $control;
	}

	// </editor-fold>
}
