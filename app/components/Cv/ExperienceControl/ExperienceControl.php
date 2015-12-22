<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Cv;
use App\Model\Entity\Referee;
use App\Model\Entity\Work;
use Nette\Utils\ArrayHash;


/**
 * ExperienceControl class
 * 
 */
class ExperienceControl extends BaseControl
{
	/** @var array */
	public $onAfterSave = [];

	/** @var Cv */
	private $cv;

	/** @var Work */
	private $experience;


	/**
	 * Renders control
	 */
	public function render() {
		$this->template->cv = $this->cv;
		parent::render();
	}
	
	/**
	 * Edits Work entity
	 * @param int $workId
	 */
	public function handleEdit($workId) {
		$this->template->activeId = $workId;
		$work = $this->em->getDao(Work::getClassName())->find($workId);
		$this->setExperience($work);
		$this->invalidateControl();
	}
	
	/**
	 * Deletes Work entity
	 * @param int $workId
	 */
	public function handleDelete($workId) {
		$workDao = $this->em->getDao(Work::getClassName());
		$work = $workDao->find($workId);
		$workDao->delete($work);
		$this->cv->deleteWork($work);
		$this->invalidateControl();
		$this->onAfterSave();
	}

	/**
	 * Creates component Form
	 * @return Form
	 */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->getElementPrototype()->addClass('ajax');
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());

		$form->addHidden('id', 0);
		$form->addText('company', 'Company name')->setRequired('Must be filled');
		$form->addDateRangePicker('season', 'Date from');
		$form->addText('position', 'Position held');
		$form->addTextArea('activities', 'Main activities and responsibilities');
		$form->addTextArea('achievment', 'Achievement');
		$form->addCheckBox('show_refree', 'Show Referee in CV');
		$form->addText('referee_name', 'Referee name');
		$form->addText('referee_position', 'Position');
		$form->addText('referee_phone', 'Phone');
		$form->addText('referee_mail', 'Email');

		$form->addSubmit('save', 'Save');
		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	/**
	 * Handler for onSuccess form's event
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function formSucceeded(Form $form, ArrayHash $values)
	{
		if($values['id'] != 0) {
			$work = $this->em->getDao(Work::getClassName())->find($values['id']);
			$this->setExperience($work);
		}
		$this->load($values);
		$this->save();
		$form->setValues(array(), true);
		$this->invalidateControl();
		$this->onAfterSave();
	}

	/**
	 * Fills Work entity by form's values
	 * @param ArrayHash $values
	 * @return \App\Components\Cv\ExperienceControl
	 */
	private function load(ArrayHash $values)
	{
		if (!$this->experience) {
			$this->experience = new Work();
		}
		$this->experience->isExperience = 1;
		$this->experience->company = $values->company;
		$this->experience->position = $values->position;
		$this->experience->dateStart = $values->season['start'];
		$this->experience->dateEnd = $values->season['end'];
		$this->experience->activities = $values->activities;
		$this->experience->achievment = $values->achievment;
		$this->experience->refereeIsPublic = (bool) $values->show_refree;
		$this->experience->referee = new Referee();
		$this->experience->referee->name = $values->referee_name;
		$this->experience->referee->position = $values->referee_position;
		$this->experience->referee->phone = $values->referee_phone;
		$this->experience->referee->mail = $values->referee_mail;
		
		$this->cv->addExperience($this->experience);
		return $this;
	}

	/**
	 * Saves Cv entity
	 * @return \App\Components\Cv\ExperienceControl
	 */
	private function save()
	{
		$cvRepo = $this->em->getRepository(Cv::getClassName());
		$cvRepo->save($this->cv);
		return $this;
	}

	/**
	 * Gets default values from entity
	 * @return array
	 */
	protected function getDefaults()
	{
		$values = [];
		if ($this->experience) {
			$values = [
				'id' => $this->experience->id,
				'company' => $this->experience->company,
				'position' => $this->experience->position,
				'season' => array('start' => $this->experience->dateStart, 'end' => $this->experience->dateEnd),
				'activities' => $this->experience->activities,
				'achievment' => $this->experience->achievment,
				'show_refree' => $this->experience->refereeIsPublic,
				'referee_name' => $this->experience->referee->name,
				'referee_position' => $this->experience->referee->position,
				'referee_phone' => $this->experience->referee->phone,
				'referee_mail' => $this->experience->referee->mail,
			];
		}
		return $values;
	}

	/**
	 * Checks if Cv entity exists
	 * @throws CvControlException
	 */
	private function checkEntityExistsBeforeRender()
	{
		if (!$this->cv) {
			throw new CvControlException('Use setCv(\App\Model\Entity\Cv) before render');
		}
	}

	/**
	 * Seter for Cv entity
	 * @param Cv $cv
	 * @return \App\Components\Cv\ExperienceControl
	 */
	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}

	/**
	 * Seter for Work entity
	 * @param Work $work
	 * @return \App\Components\Cv\ExperienceControl
	 */
	public function setExperience(Work $experience)
	{
		$this->experience = $experience;
		return $this;
	}
}

interface IExperienceControlFactory
{

	/** @return ExperienceControl */
	function create();
}
