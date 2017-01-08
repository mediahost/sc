<?php

namespace App\Components\Company;

use App\Components\BaseControl;
use App\Components\BaseControlException;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Company;
use Nette\Utils\ArrayHash;

class Photo extends BaseControl
{

	/** @var array */
	public $onAfterSave = [];

	/** @var Company */
	public $company;

	/** @var bool */
	private $editable = FALSE;

	public function render()
	{
		$this->template->company = $this->company;
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

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->save();
		$this->onAfterSave($this->company);
	}

	protected function load(ArrayHash $values)
	{
		if ($values->photo->isImage()) {
			$this->company->logo = $values->photo;
		}
		return $this;
	}

	protected function save()
	{
		$this->em->persist($this->company);
		$this->em->flush();
		return $this;
	}

	protected function getDefaults()
	{
		$values = [
			'photo' => $this->company->logo,
		];
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->company) {
			throw new BaseControlException('Use setPerson(\App\Model\Entity\Person) before render');
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	public function setCompany(Company $company)
	{
		$this->company = $company;
		return $this;
	}

	public function canEdit($value = TRUE)
	{
		$this->editable = $value;
		return $this;
	}

	// </editor-fold>
}

interface IPhotoFactory
{

	/** @return Photo */
	function create();
}
