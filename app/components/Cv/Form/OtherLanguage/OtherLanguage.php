<?php

namespace App\Components\Cv;

use App\Forms\Form;
use App\Model\Entity;
use Nette\Http\Request;
use Nette\Utils\ArrayHash;

class OtherLanguage extends CvForm
{
	/** @var Language */
	private $language;

	/** @var bool */
	private $editMode = false;


	public function __construct(Request $httpRequest)
	{
		if (isset($httpRequest->post['id'])  &&  $httpRequest->post['id'] > 0) {
			$this->editMode = true;
		}
	}

	public function render()
	{
		$this->template->cv = $this->cv;
		$this->template->language = $this->language;
		$this->disableUsedLanguages();
		parent::render();
	}

	public function handleEdit($langId)
	{
		$this->editMode = true;
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
		$language = $form->addSelect2('language', 'Language', Entity\Language::getLanguagesList());
		if ($this->editMode) {
			$language->setDisabled();
		}
		$form->addHidden('listening', 'Listening');
		$form->addHidden('reading', 'Reading');
		$form->addHidden('interaction', 'Spoken Interaction');
		$form->addHidden('production', 'Spoken Production');
		$form->addHidden('writing', 'Writing');
		$form->setDefaults($this->getDefaults());
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		if ($values['id'] != 0) {
			$lang = $this->em->getDao(Entity\Language::getClassName())->find($values['id']);
			$this->setLanguage($lang);
		}
		$form->setValues([], true);
		$this->load($values);
		$this->save();
		$this->redrawControl();
		$this->onAfterSave($this->cv);
	}

	private function load(ArrayHash $values)
	{
		if (!$this->language) {
			$this->language = new Entity\Language();
		}
		if (isset($values->language)) {
			$this->language->language = $values->language;
		}
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

	private function disableUsedLanguages()
	{
		if (!$this['form']['language']->isDisabled()) {
			$disabledLang = [];
			if (is_array($this->cv->languages)) {
				foreach ($this->cv->languages as $lng) {
					$disabledLang[] = $lng->language;
				}
			}
			$this['form']['language']->setDisabled($disabledLang);
		}
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
