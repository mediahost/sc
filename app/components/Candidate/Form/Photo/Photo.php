<?php

namespace App\Components\Candidate;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Candidate;
use App\Model\Entity\Image;
use Nette\Utils\ArrayHash;

class Photo extends BaseControl
{

	/** @var Candidate */
	public $candidate;

	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>

	public function render()
	{
		$this->template->photo = ($this->candidate->photo) ?
			$this->candidate->photo : Image::DEFAULT_IMAGE;
		parent::render();
	}

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addUpload('photo', 'Photo')
			//->setPreview('/foto/200-200/' . ($this->candidate->photo ? $this->candidate->photo : Image::DEFAULT_IMAGE), $this->candidate->name)
			//->setSize(200, 200)
			->addCondition(Form::FILLED)
			->addRule(Form::IMAGE, 'Photo must be valid image');

		$form->addCheckBox('showPhoto', 'Show photo in CV');

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->save();
		$this->onAfterSave($this->candidate);
	}

	protected function load(ArrayHash $values)
	{
		if ($values->photo->isImage()) {
			$this->candidate->photo = $values->photo;
		}
		return $this;
	}

	protected function save()
	{
		$this->em->persist($this->candidate);
		$this->em->flush();
		return $this;
	}

	/** @return array */
	protected function getDefaults()
	{
		$values = [
			'photo' => $this->candidate->photo,
		];
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->candidate) {
			throw new CandidateException('Use setCandidate(\App\Model\Entity\Candidate) before render');
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	public function setCandidate(Candidate $candidate)
	{
		$this->candidate = $candidate;
		return $this;
	}

	// </editor-fold>
}

interface IPhotoFactory
{

	/** @return Photo */
	function create();
}
