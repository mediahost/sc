<?php

Namespace App\Components\User;

/* Nette */

use Nette\Application\UI,
	Kdyby\Doctrine\EntityManager,
	Kdyby\Doctrine\EntityDao;

/* Application */
use App\Components,
	App\Model\Facade,
	App\Model\Entity;

/**
 * Form with all user's personal settings.
 */
class SettingsControl extends Components\BaseControl
{

	// <editor-fold defaultstate="collapsed" desc="constants & variables">
	
	public $onSave = [];

	/** @var EntityManager */
	private $em;

	/** @var EntityDao */
	private $userDao;

	/** @var Facade\UserFacade @inject */
	public $userFacade;

	/** @var Facade\AuthFacade @inject */
	public $authFacade;

	/** @var \Nette\Mail\IMailer @inject */
	public $mailer;

	/** @var \App\Model\Storage\MessageStorage @inject */
	public $messages;

	/** @var \App\Model\Storage\UserSettingsStorage @inject */
	public $settingsStorage;

	// </editor-fold>

	/**
	 * @return UI\Form
	 */
	protected function createComponentForm()
	{
		$form = new UI\Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new \App\Forms\Renderers\MetronicFormRenderer());

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

	// <editor-fold defaultstate="collapsed" desc="setters">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="renderers">
	// </editor-fold>
}

interface ISettingsControlFactory
{

	/** @return SettingsControl */
	function create();
}