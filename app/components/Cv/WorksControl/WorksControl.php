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
 * WorksControl class
 */
class WorksControl extends BaseControl
{
	/** @var array */
	public $onAfterSave = [];

	/** @var Cv */
	private $cv;

	/** @var Work */
	private $work;

	
	/**
	 * Renders control
	 */
	public function render() {
		$this->template->cv = $this->cv;
		$this->template->work = $this->work;
		parent::render();
	}

	/**
	 * Edits Work entity
	 * @param int $workId
	 */
	public function handleEdit($workId) {
		$this->template->activeId = $workId;
		$workDao = $this->em->getDao(Work::getClassName());
		$work = $workDao->find($workId);
		$this->setWork($work);
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
		$form->addText('company', 'Company')->setRequired('Must be filled');
		$form->addDateRangePicker('season', 'Date from');
		$form->addText('position', 'Position held');
		$form->addTextArea('activities', 'Main activities and responsibilities');
		$form->addTextArea('achievment', 'Achievement');
		$form->addCheckBox('show_refree', 'Show Referee in CV');
		$form->addText('referee_name', 'Referee name');
		$form->addText('referee_position', 'Position');
		$form->addText('referee_phone', 'Phone');
		$form->addText('referee_mail', 'Email')
			->addRule(Form::EMAIL, 'Entered value is not email!');

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
			$this->setWork($work);
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
	 * @return \App\Components\Cv\WorksControl
	 */
	private function load(ArrayHash $values)
	{
		if (!$this->work) {
			$this->work = new Work();
		}
		$this->work->company = $values->company;
		$this->work->position = $values->position;
		$this->work->dateStart = $values->season['start'];
		$this->work->dateEnd = $values->season['end'];
		$this->work->activities = $values->activities;
		$this->work->achievment = $values->achievment;
		$this->work->refereeIsPublic = (bool) $values->show_refree;
		$this->work->referee = new Referee();
		$this->work->referee->name = $values->referee_name;
		$this->work->referee->position = $values->referee_position;
		$this->work->referee->phone = $values->referee_phone;
		$this->work->referee->mail = $values->referee_mail;
		
		$this->cv->addWork($this->work);
		return $this;
	}

	/**
	 * Saves Cv entity
	 * @return \App\Components\Cv\EducationsControl
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
		if ($this->work) {
			$values = [
				'id' => $this->work->id,
				'company' => $this->work->company,
				'position' => $this->work->position,
				'season' => array('start' => $this->work->dateStart, 'end' => $this->work->dateEnd),
				'activities' => $this->work->activities,
				'achievment' => $this->work->achievment,
				'show_refree' => $this->work->refereeIsPublic,
				'referee_name' => $this->work->referee->name,
				'referee_position' => $this->work->referee->position,
				'referee_phone' => $this->work->referee->phone,
				'referee_mail' => $this->work->referee->mail,
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
	 * @return \App\Components\Cv\WorksControl
	 */
	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}

	/**
	 * Seter for Work entity
	 * @param Work $work
	 * @return \App\Components\Cv\WorksControl
	 */
	public function setWork(Work $work)
	{
		$this->work = $work;
		return $this;
	}
}

/**
 * Definition IWorksControlFactory
 * 
 */
interface IWorksControlFactory
{

	/** @return WorksControl */
	function create();
}
