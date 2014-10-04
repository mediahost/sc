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
		$settings = $this->settingsStorage->settings;
		$settings->language = $values->language;
		
		$this->settingsStorage->save($this->presenter->user->id, $settings);
		
		$this->presenter->flashMessage('Your settings has been saved.', 'success');
		$this->presenter->redirect('this#personal-settings',['lang' => $settings->language]);
	}

}

interface ISettingsControlFactory
{

	/** @return SettingsControl */
	function create();
}
