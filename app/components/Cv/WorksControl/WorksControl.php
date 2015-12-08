<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Cv;
use App\Model\Entity\Referee;
use App\Model\Entity\Work;
use Nette\Utils\ArrayHash;
use App\Forms\Renderers\Bootstrap3FormRenderer;

class WorksControl extends BaseControl
{
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var Cv */
	private $cv;

	/** @var Work */
	private $work;

	// </editor-fold>
	
	
	/**
	 * Renders control
	 */
	public function render() {
		$this->template->cv = $this->cv;
		$this->template->work = $this->work;
		parent::render();
	}

	/**
	 * Edit work
	 * @param int $workId
	 */
	public function handleEdit($workId) {
		$this->template->activeId = $workId;
		$workDao = $this->em->getDao(Work::getClassName());
		$work = $workDao->find($workId);
		$this->setWork($work);
		$this->invalidateControl('worksControl');
	}
	
	/**
	 * Delete work
	 * @param int $workId
	 */
	public function handleDelete($workId) {
		$workDao = $this->em->getDao(Work::getClassName());
		$cvRepo = $this->em->getRepository(Cv::getClassName());
		$work = $workDao->find($workId);
		$workDao->delete($work);
		$this->cv->deleteWork($work);
		$this->invalidateControl('worksControl');
	}

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->addHidden('id', 0);
		$form->getElementPrototype()->addClass('ajax');

		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());

		$form->addText('company', 'Company')
				->setRequired('Must be filled');
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

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		if($values['id'] != 0) {
			$workDao = $this->em->getDao(Work::getClassName());
			$work = $workDao->find($values['id']);
			$this->setWork($work);
		}
		$this->load($values);
		$this->save();
		$this->invalidateControl();
		//$this->onAfterSave($this->cv);
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

	public function setWork(Work $work)
	{
		$this->work = $work;
		return $this;
	}

	// </editor-fold>
}

interface IWorksControlFactory
{

	/** @return WorksControl */
	function create();
}
