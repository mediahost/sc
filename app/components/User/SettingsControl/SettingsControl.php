<?php

namespace App\Components\User;

use App\Components;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Facade;
use App\Model\Storage\UserSettingsStorage;
use Nette\Application\UI;

/**
 * Form with all user's personal settings.
 */
class SettingsControl extends Components\BaseControl
{

	public $onSave = [];

	/** @var UserSettingsStorage @inject */
	public $settingsStorage;

	/** @return UI\Form */
	protected function createComponentForm()
	{
		$form = new UI\Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addSelect('language', 'Language', [
			'en' => 'English',
			'cs' => 'Čeština'
		]);

		$form->addSubmit('save', 'Save');

		$form->setDefaults([
			'language' => $this->settingsStorage->language,
		]);
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(UI\Form $form, $values)
	{
		$this->settingsStorage
				->setLanguage($values->language)
				->save();

		$this->presenter->flashMessage('Your settings has been saved.', 'success');
		$this->presenter->redirect('this#personal-settings', [
			'lang' => $this->settingsStorage->language,
		]);
	}
}

interface ISettingsControlFactory
{

	/** @return SettingsControl */
	function create();
}
