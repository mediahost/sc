<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Cv;
use App\Model\Entity\Language;
use Nette\Utils\ArrayHash;

class OtherLanguageControl extends BaseControl
{
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var Cv */
	private $cv;

	/** @var Language */
	private $language;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();

		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addSelect2('language', 'Language', Language::getLanguagesList())
				->setRequired('Please select language');
		
		$form->addSlider('listening', 'Listening', Language::getLanguageLevelList());
		$form->addSlider('reading', 'Reading', Language::getLanguageLevelList());
		$form->addSlider('interaction', 'Spoken Interaction', Language::getLanguageLevelList());
		$form->addSlider('production', 'Spoken Production', Language::getLanguageLevelList());
		$form->addSlider('writing', 'Writing', Language::getLanguageLevelList());
		
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
		if (!$this->language) {
			$this->language = new Language();
		}
		$this->language->language = $values->language;
		$this->language->listening = $values->listening;
		$this->language->reading = $values->reading;
		$this->language->spokenInteraction = $values->interaction;
		$this->language->spokenProduction = $values->production;
		$this->language->writing = $values->writing;
		
		$this->cv->addLanguage($this->language);
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
		if ($this->language) {
			$values = [
				'language' => $this->language->language,
				'listening' => $this->language->listening,
				'reading' => $this->language->reading,
				'interaction' => $this->language->spokenInteraction,
				'production' => $this->language->spokenProduction,
				'writing' => $this->language->writing,
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

	public function setLanguage(Language $lang)
	{
		$this->language = $lang;
		return $this;
	}

	// </editor-fold>
}

interface IOtherLanguageControlFactory
{

	/** @return OtherLanguageControl */
	function create();
}
