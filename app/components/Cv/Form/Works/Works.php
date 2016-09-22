<?php

namespace App\Components\Cv;

use App\Forms\Form;
use App\Model\Entity\Referee;
use App\Model\Entity\Work;
use Nette\Utils\ArrayHash;

class Works extends CvForm
{
	/** @var Work */
	private $work;

	public function render()
	{
		$this->template->cv = $this->cv;
		$this->template->work = $this->work;
		parent::render();
	}

	public function handleEdit($workId)
	{
		$this->template->activeId = $workId;
		$workDao = $this->em->getDao(Work::getClassName());
		$work = $workDao->find($workId);
		$this->setWork($work);
		$this->redrawControl();
	}

	public function handleDelete($workId)
	{
		$workDao = $this->em->getDao(Work::getClassName());
		$work = $workDao->find($workId);
		$workDao->delete($work);
		$this->cv->deleteWork($work);
		$this->redrawControl();
		$this->onAfterSave();
	}

	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();
		$form = $this->createFormInstance();
		$form->addHidden('id', 0);
		$form->addText('company', 'Company')->setRequired('Must be filled');
		$form->addDateRangePicker('season', 'Date from');
		$form->addText('position', 'Position held');
		$form->addTextArea('activities', 'Main activities and responsibilities');
		$form->addTextArea('achievement', 'Achievement');
		$form->addCheckBox('show_refree', 'Show Referee in CV');
		$form->addText('referee_name', 'Referee name');
		$form->addText('referee_position', 'Position');
		$form->addText('referee_phone', 'Phone');
		$form->addText('referee_mail', 'Email')->addRule(Form::EMAIL, 'Entered value is not email!');
		$form->setDefaults($this->getDefaults());
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		if ($values['id'] != 0) {
			$work = $this->em->getDao(Work::getClassName())->find($values['id']);
			$this->setWork($work);
		}
		$form->setValues([], true);
		$this->load($values);
		$this->save();
		$this->redrawControl();
		$this->onAfterSave();
	}

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
		$this->work->achievment = $values->achievement;
		$this->work->refereeIsPublic = (bool)$values->show_refree;
		$this->work->referee = new Referee();
		$this->work->referee->name = $values->referee_name;
		$this->work->referee->position = $values->referee_position;
		$this->work->referee->phone = $values->referee_phone;
		$this->work->referee->mail = $values->referee_mail;

		$this->cv->addWork($this->work);
		return $this;
	}

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
				'achievement' => $this->work->achievment,
				'show_refree' => $this->work->refereeIsPublic,
				'referee_name' => $this->work->referee->name,
				'referee_position' => $this->work->referee->position,
				'referee_phone' => $this->work->referee->phone,
				'referee_mail' => $this->work->referee->mail,
			];
		}
		return $values;
	}

	public function setWork(Work $work)
	{
		$this->work = $work;
		return $this;
	}
}

interface IWorksFactory
{

	/** @return Works */
	function create();
}
