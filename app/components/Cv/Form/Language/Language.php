<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity;
use Nette\Utils\ArrayHash;

class Language extends BaseControl
{
	/** @var array */
	public $onAfterSave = [];

	/** @var Entity\Cv */
	private $cv;

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

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());

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
		$this->invalidateControl();
		$this->onAfterSave();
	}

	private function load(ArrayHash $values)
	{
		$this->cv->motherLanguage = $values->motherTongue;
		return $this;
	}

	private function save()
	{
		$cvRepo = $this->em->getRepository(Entity\Cv::getClassName());
		$cvRepo->save($this->cv);
		return $this;
	}

	protected function getDefaults()
	{
		$values = [];
		$values['motherTongue'] = $this->cv->motherLanguage;
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->cv) {
			throw new CvException('Use setCv(\App\Model\Entity\Cv) before render');
		}
	}

	public function setCv(Entity\Cv $cv)
	{
		$this->cv = $cv;
		return $this;
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
