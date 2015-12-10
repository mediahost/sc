<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Cv;
use App\Model\Entity\Language;
use Nette\Utils\ArrayHash;

class LanguageControl extends BaseControl
{
	/** @var array */
	public $onAfterSave = [];

	/** @var Cv */
	private $cv;
	
	/** @var Language */
	private $language;

	
	/**
	 * Renders control
	 */
	public function render() {
		$this->template->cv = $this->cv;
		$this->template->language = $this->language;
		parent::render();
	}
	
	/**
	 * Edits Language entity
	 * @param int $langId
	 */
	public function handleEdit($langId) {
		$this->template->activeId = $langId;
		$langDao = $this->em->getDao(Language::getClassName());
		$lang = $langDao->find($langId);
		$this->setLanguage($lang);
		$this->invalidateControl();
	}
	
	/**
	 * Deletes Language entity
	 * @param int $langId
	 */
	public function handleDelete($langId) {
		$langDao = $this->em->getDao(Language::getClassName());
		$lang = $langDao->find($langId);
		$langDao->delete($lang);
		$this->cv->deleteLanguage($lang);
		$this->invalidateControl();
	}
	
	/**
	 * Creates component Form
	 * @return Form
	 */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());

		$form->addSelect2('motherTongue', 'Mother tongue', Language::getLanguagesList())
				->setPrompt('Not Disclosed');
		$form->addSelect2('language', 'Language', Language::getLanguagesList())
				->setRequired('Please select language');
		$form->addHidden('listening', 'Listening');
		$form->addHidden('reading', 'Reading');
		$form->addHidden('interaction', 'Spoken Interaction');
		$form->addHidden('production', 'Spoken Production');
		$form->addHidden('writing', 'Writing');
		
		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	/**
	 * Handler for onSuccess form's event
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$this->load($values);
		$this->save();
		$this->invalidateControl();
	}

	/**
	 * Fills Language entity by form's values
	 * @param ArrayHash $values
	 * @return \App\Components\Cv\LanguageControl
	 */
	private function load(ArrayHash $values)
	{
		$this->cv->motherLanguage = $values->motherTongue;
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

	/**
	 * Saves Cv entity
	 * @return \App\Components\Cv\LanguageControl
	 */
	private function save()
	{
		$cvRepo = $this->em->getRepository(Cv::getClassName());
		$cvRepo->save($this->cv);
		return $this;
	}

	/**
	 * Gets default values from entity
	 * @return array
	 */
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
				'writing' => $this->language->writing
			];
		}
		$values['motherTongue'] = $this->cv->motherLanguage;
		return $values;
	}

	/**
	 * Checks if Cv entity exists
	 * @throws CvControlException
	 */
	private function checkEntityExistsBeforeRender()
	{
		if (!$this->cv) {
			throw new CvControlException('Use setCv(\App\Model\Entity\Cv) before render');
		}
	}

	/**
	 * Seter for Cv entity
	 * @param Cv $cv
	 * @return \App\Components\Cv\WorksControl
	 */
	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}
	
	/**
	 * Seter for Language entity
	 * @param Language $lang
	 * @return \App\Components\Cv\LanguageControl
	 */
	public function setLanguage(Language $lang)
	{
		$this->language = $lang;
		return $this;
	}
}

/**
 * Definition ILanguageControlFactory
 * 
 */
interface ILanguageControlFactory
{

	/** @return LanguageControl */
	function create();
}
