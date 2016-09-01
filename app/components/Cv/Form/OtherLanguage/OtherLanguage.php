<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity;
use Nette\Utils\ArrayHash;

class OtherLanguage extends BaseControl
{
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var Entity\Cv */
	private $cv;

	/** @var Language */
	private $language;

	// </editor-fold>

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
		$this->invalidateControl();
	}

	public function handleDelete($langId)
	{
		$langDao = $this->em->getDao(Entity\Language::getClassName());
		$lang = $langDao->find($langId);
		$langDao->delete($lang);
		$this->cv->deleteLanguage($lang);
		$this->invalidateControl();
		$this->onAfterSave();
	}

	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();

		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

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
		$this->invalidateControl();
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

	private function save()
	{
		$cvRepo = $this->em->getRepository(Entity\Cv::getClassName());
		$cvRepo->save($this->cv);
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

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->cv) {
			throw new CvException('Use setCv(\App\Model\Entity\Cv) before render');
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	public function setCv(Entity\Cv $cv)
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

interface IOtherLanguageFactory
{

	/** @return OtherLanguage */
	function create();
}
