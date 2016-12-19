<?php

namespace App\Components\Candidate;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Candidate;

class ProfileId extends BaseControl
{
	/** @var array */
	public $onAfterSave = [];

	/** @var Candidate */
	private $candidate;

	/** @var bool */
	private $editable = FALSE;


	public function render()
	{
		$this->template->editable = $this->editable;
		$this->template->candidate = $this->candidate;
		parent::render();
	}

	public function handleEdit()
	{
		$this->setTemplateFile('default');
		$this->redrawControl('profileId');
	}

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addText('profileId', $this->presenter->link('//:Front:Profile:', ['backlink' => NULL]))
			->addRule(Form::MIN_LENGTH, 'Minimum is %d charaters', 5)
			->addRule(Form::MAX_LENGTH, 'Maximum is %d charaters', 64)
			->addRule(Form::PATTERN, 'Insert only characters and numbers', '[\d\w\-]+');
		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->candidate->profileId = $values->profileId;
		$this->em->persist($this->candidate);
		$this->em->flush();
		$this->onAfterSave($this->candidate);
	}

	protected function getDefaults()
	{
		return [
			'profileId' => $this->candidate->profileId
		];
	}

	public function setCandidate(Candidate $candidate)
	{
		$this->candidate = $candidate;
		return $this;
	}

	public function canEdit($value = TRUE)
	{
		$this->editable = $value;
		return $this;
	}

	public function setTemplateFile($name)
	{
		return parent::setTemplateFile($name);
	}
}

interface IProfileIdFactory
{
	/** @return ProfileId */
	public function create();
}