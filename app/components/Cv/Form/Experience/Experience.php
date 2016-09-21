<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Model\Entity\Cv;
use App\Model\Entity\Referee;
use App\Model\Entity\Work;
use Nette\Utils\ArrayHash;

class Experience extends BaseControl
{
	/** @var array */
	public $onAfterSave = [];

	/** @var Cv */
	private $cv;

	/** @var Work */
	private $experience;

	public function render()
	{
		$this->template->cv = $this->cv;
		parent::render();
	}

	public function handleEdit($workId)
	{
		$this->template->activeId = $workId;
		$work = $this->em->getDao(Work::getClassName())->find($workId);
		$this->setExperience($work);
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
		$form->addText('company', 'Company name')->setRequired('Must be filled');
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

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		if ($values['id'] != 0) {
			$work = $this->em->getDao(Work::getClassName())->find($values['id']);
			$this->setExperience($work);
		}
		$this->load($values);
		$this->save();
		$form->setValues(array(), true);
		$this->redrawControl();
		$this->onAfterSave();
	}

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
		$this->experience->refereeIsPublic = (bool)$values->show_refree;
		$this->experience->referee = new Referee();
		$this->experience->referee->name = $values->referee_name;
		$this->experience->referee->position = $values->referee_position;
		$this->experience->referee->phone = $values->referee_phone;
		$this->experience->referee->mail = $values->referee_mail;

		$this->cv->addExperience($this->experience);
		return $this;
	}

	private function save()
	{
		$cvRepo = $this->em->getRepository(Cv::getClassName());
		$cvRepo->save($this->cv);
		return $this;
	}

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

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->cv) {
			throw new CvException('Use setCv(\App\Model\Entity\Cv) before render');
		}
	}

	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}

	public function setExperience(Work $experience)
	{
		$this->experience = $experience;
		return $this;
	}
}

interface IExperienceFactory
{

	/** @return Experience */
	function create();
}
