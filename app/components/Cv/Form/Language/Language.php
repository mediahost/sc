<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity;
use Nette\Utils\ArrayHash;

class Language extends CvForm
{
	/** @var Entity\Language */
	private $language;

	public function render()
	{
		$this->template->cv = $this->cv;
		$this->template->language = $this->language;
		parent::render();
	}

	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();
		$form = $this->createFormInstance();
		$form->addSelect('motherTongue', 'Mother tongue', Entity\Language::getLanguagesList());
		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$this->load($values);
		$this->save();
		$form->setValues(array('motherTongue' => $this->cv->motherLanguage), true);
		$this->redrawControl();
		$this->onAfterSave();
	}

	private function load(ArrayHash $values)
	{
		$this->cv->motherLanguage = $values->motherTongue;
		return $this;
	}

	protected function getDefaults()
	{
		$values = [];
		$values['motherTongue'] = $this->cv->motherLanguage;
		return $values;
	}

	public function setLanguage(Entity\Language $lang)
	{
		$this->language = $lang;
		return $this;
	}
}

interface ILanguageFactory
{

	/** @return Language */
	function create();
}
