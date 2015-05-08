<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Cv;
use App\Model\Entity\Referee;
use App\Model\Entity\Work;
use Nette\Utils\ArrayHash;

class ExperienceControl extends BaseControl
{
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var Cv */
	private $cv;

	/** @var Work */
	private $experience;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();

		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addText('company', 'Company name')
				->setRequired('Must be filled');
		$form->addText('position', 'Position held');
		$form->addDatePicker('date_from', 'Date from');
		$form->addDatePicker('date_to', 'Date to');
		$form->addTextArea('activities', 'Main activities and responsibilities');
		$form->addTextArea('achievments', 'Achievement');
		
		$form->addCheckSwitch('show_refree', 'Show Referee in CV')
				->setOffText('No')
				->setOnText('Yes');
		$form->addText('referee_name', 'Referee name');
		$form->addText('referee_position', 'Position');
		$form->addText('referee_phone', 'Phone');
		$form->addText('referee_mail', 'Email');

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$this->load($values);
		$this->save();
		$this->onAfterSave($this->cv);
	}

	private function load(ArrayHash $values)
	{
		if (!$this->experience) {
			$this->experience = new Work();
		}
		$this->experience->company = $values->company;
		$this->experience->position = $values->position;
		$this->experience->dateStart = $values->date_from;
		$this->experience->dateEnd = $values->date_to;
		$this->experience->refereeIsPublic = (bool) $values->show_refree;
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

	/** @return array */
	protected function getDefaults()
	{
		$values = [];
		if ($this->experience) {
			$values = [
				'company' => $this->experience->company,
				'position' => $this->experience->position,
				'date_from' => $this->experience->dateStart,
				'date_to' => $this->experience->dateEnd,
				'activities' => $this->experience->activities,
				'achievments' => $this->experience->achievment,
				
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
			throw new CvControlException('Use setCv(\App\Model\Entity\Cv) before render');
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

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

	// </editor-fold>
}

interface IExperienceControlFactory
{

	/** @return ExperienceControl */
	function create();
}
