<?php

namespace App\Components\User;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Storage\UserSettingsStorage;

/**
 * Form with user's personal settings.
 */
class PreferencesControl extends BaseControl
{
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var UserSettingsStorage @inject */
	public $settingsStorage;
	
	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addSelect2('language', 'Language', [
			'en' => 'English',
			'cs' => 'Čeština'
		]);

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->settingsStorage
				->setLanguage($values->language)
				->save();

		$savedLanguage = $this->settingsStorage->language;
		$this->onAfterSave($savedLanguage);
	}

	/**
	 * Get Entity for Form
	 * @return array
	 */
	private function getDefaults()
	{
		$values = [
			'language' => $this->settingsStorage->language,
		];
		return $values;
	}

}

interface IPreferencesControlFactory
{

	/** @return PreferencesControl */
	function create();
}
