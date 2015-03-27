<?php

namespace App\Components\Job;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Job;
use App\Model\Facade\CompanyFacade;
use Nette\Utils\ArrayHash;

/**
 * Job info form
 */
class BasicInfoControl extends BaseControl
{

	/** @var Job */
	private $job;

	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="injects">

	/** @var CompanyFacade @inject */
	public $companyFacade;

	// </editor-fold>
	// <editor-fold defaultstate="expanded" desc="variables">
	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer);

		$form->addText('name', 'Name')
				->setAttribute('placeholder', 'Job title')
				->setRequired('Please enter job\'s name.');
		$form->addTextArea('description', 'Description')
				->setAttribute('placeholder', 'Job description');

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->save();
		$this->onAfterSave($this->job);
	}

	protected function load(ArrayHash $values)
	{
		$this->job->name = $values->name;
		$this->job->description = $values->description;
		return $this;
	}

	private function save()
	{
		$cvRepo = $this->em->getRepository(Job::getClassName());
		$cvRepo->save($this->job);
		return $this;
	}

	/** @return array */
	protected function getDefaults()
	{
		$values = [
			'name' => $this->job->name,
			'description' => $this->job->description,
		];
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->job) {
			throw new JobControlException('Use setJob(\App\Model\Entity\Job) before render');
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	public function setJob(Job $job)
	{
		$this->job = $job;
		return $this;
	}

	// </editor-fold>
}

interface IBasicInfoControlFactory
{

	/** @return BasicInfoControl */
	function create();
}
