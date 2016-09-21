<?php

namespace App\Components\Cv;

use App\Forms\Form;
use App\Model\Entity;
use Nette\Utils\ArrayHash;

class OtherLanguage extends CvForm
{
	/** @var Language */
	private $language;


	public function render()
	{
		$this->template->cv = $this->cv;
		$this->template->language = $this->language;
		$this->setDisabled();
		parent::render();
	}

	public function handleEdit($langId)
	{
		$this->template->activeId = $langId;
		$langDao = $this->em->getDao(Entity\Language::getClassName());
		$lang = $langDao->find($langId);
		$this->setLanguage($lang);
		$this->redrawControl();
	}

	public function handleDelete($langId)
	{
		$langDao = $this->em->getDao(Entity\Language::getClassName());
		$lang = $langDao->find($langId);
		$langDao->delete($lang);
		$this->cv->deleteLanguage($lang);
		$this->redrawControl();
		$this->onAfterSave();
	}

	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();
		$form = $this->createFormInstance();

		$form->addHidden('id', 0);
		$form->addSelect2('language', 'Language', Entity\Language::getLanguagesList());
		$form->addHidden('listening', 'Listening');
		$form->addHidden('reading', 'Reading');
		$form->addHidden('interaction', 'Spoken Interaction');
		$form->addHidden('production', 'Spoken Production');
		$form->addHidden('writing', 'Writing');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		if ($values['id'] != 0) {
			$lang = $this->em->getDao(Entity\Language::getClassName())->find($values['id']);
			$this->setLanguage($lang);
		}
		$this->load($values);
		$this->save();
		$form->setValues([], true);
		$this->redrawControl();
		$this->onAfterSave($this->cv);
	}

	private function load(ArrayHash $values)
	{
		if (!$this->language) {
			$this->language = new Entity\Language();
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

	protected function getDefaults()
	{
		$values = [];
		if ($this->language) {
			$values = [
				'id' => $this->language->id,
				'language' => $this->language->language,
				'listening' => $this->language->listening,
				'reading' => $this->language->reading,
				'interaction' => $this->language->spokenInteraction,
				'production' => $this->language->spokenProduction,
				'writing' => $this->language->writing
			];
		}
		return $values;
	}

	private function setDisabled()
	{
		if ($this->language) {
			$this['form']['language']->setAttribute('disabled');
			$this['form']['language']->setDefaultValue($this->language->language);
			return;
		}

		$disabledLang = [];
		if (is_array($this->cv->languages)) {
			foreach ($this->cv->languages as $lng) {
				$disabledLang[] = $lng->language;
			}
		}
		$this['form']['language']->setDisabled($disabledLang);
	}

	public function setLanguage(Entity\Language $lang)
	{
		$this->language = $lang;
		return $this;
	}
}

interface IOtherLanguageFactory
{

	/** @return OtherLanguage */
	function create();
}
