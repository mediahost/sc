<?php

namespace App\Components\Candidate;

use App\Components\BaseControl;
use App\Components\BaseControlException;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Image;
use App\Model\Entity\Person;
use Nette\Utils\ArrayHash;

class Photo extends BaseControl
{

	/** @var Person */
	public $person;

	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>

	public function render()
	{
		$this->template->person = $this->person;
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
		$this->onAfterSave($this->person);
	}

	protected function load(ArrayHash $values)
	{
		if ($values->photo->isImage()) {
			$this->person->photo = $values->photo;
		}
		return $this;
	}

	protected function save()
	{
		$this->em->persist($this->person);
		$this->em->flush();
		return $this;
	}

	/** @return array */
	protected function getDefaults()
	{
		$values = [
			'photo' => $this->person->photo,
		];
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->person) {
			throw new BaseControlException('Use setPerson(\App\Model\Entity\Person) before render');
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	public function setPerson(Person $person)
	{
		$this->person = $person;
		return $this;
	}

	// </editor-fold>
}

interface IPhotoFactory
{

	/** @return Photo */
	function create();
}
