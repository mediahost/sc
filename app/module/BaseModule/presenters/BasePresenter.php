<?php

namespace App\BaseModule\Presenters;

use App\Components\Auth\ISignOutControlFactory;
use App\Components\Auth\SignOutControl;
use App\Extensions\Settings\Model\Service\DesignService;
use App\Extensions\Settings\Model\Service\LanguageService;
use App\Extensions\Settings\Model\Storage\DefaultSettingsStorage;
use App\Model\Entity;
use App\Model\Facade\UserFacade;
use App\TaggedString;
use GettextTranslator\Gettext;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Presenter;
use Nette\Security\Identity;
use Nette\Security\IUserStorage;
use WebLoader\LoaderFactory;
use WebLoader\Nette\CssLoader;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter
{

	/** @persistent */
	public $lang = 'en';

	/** @persistent */
	public $backlink = '';

	// <editor-fold defaultstate="collapsed" desc="injects">

	/** @var LoaderFactory @inject */
	public $webLoader;

	/** @var ISignOutControlFactory @inject */
	public $iSignOutControlFactory;

	/** @var Gettext @inject */
	public $translator;

	/** @var DefaultSettingsStorage @inject */
	public $settingStorage;

	/** @var DesignService @inject */
	public $designService;

	/** @var LanguageService @inject */
	public $languageService;

	/** @var EntityManager @inject */
	public $em;

	/** @var UserFacade @inject */
	public $userFacade;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->loadUserSettings();
		$this->setLang();
	}

	protected function beforeRender()
	{
		$this->template->lang = $this->lang;
		$this->template->setTranslator($this->translator);
		$this->template->designSettings = $this->designService->settings;
	}

	// <editor-fold defaultstate="collapsed" desc="flash messages">

	/**
	 * Translate flash messages if not HTML
	 * @param type $message
	 * @param type $type
	 */
	public function flashMessage($message, $type = 'info')
	{
		if (is_string($message)) {
			$message = $this->translator->translate($message);
		} else if ($message instanceof TaggedString) {
			$message->setTranslator($this->translator);
			$message = (string) $message;
		}
		parent::flashMessage($message, $type);
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="requirments">

	public function checkRequirements($element)
	{
		$secured = $element->getAnnotation('secured');
		$resource = $element->getAnnotation('resource');
		$privilege = $element->getAnnotation('privilege');

		if ($secured) {
			if (!$this->user->loggedIn) {
				if ($this->user->logoutReason === IUserStorage::INACTIVITY) {
					$this->flashMessage('You have been signed out, because you have been inactive for long time.');
					$this->redirect(':Front:LockScreen:', ['backlink' => $this->storeRequest()]);
				} else {
					$this->flashMessage('You should be logged in!');
					$this->redirect(':Front:Sign:in', ['backlink' => $this->storeRequest()]);
				}
			} elseif (!$this->user->isAllowed($resource, $privilege)) {
				throw new ForbiddenRequestException;
			}
		}
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="settings">

	protected function loadUserSettings()
	{
		if ($this->user->loggedIn && $this->user->id) {
			$userDao = $this->em->getDao(Entity\User::getClassName());
			$userEntity = $userDao->find($this->user->id);
			$this->user->login(new Identity($userEntity->id, $userEntity->getRolesPairs(), $userEntity->toArray()));
		}
		$this->settingStorage->identity = $this->user;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="language">

	private function setLang()
	{
		if (!$this->lang) {
			$this->lang = $this->languageService->language;
		}
		$this->translator->setLang($this->lang);
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="components">

	/** @return SignOutControl */
	public function createComponentSignOut()
	{
		return $this->iSignOutControlFactory->create();
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="css webloader">

	/** @return CssLoader */
	protected function createComponentCssFront()
	{
		$css = $this->webLoader->createCssLoader('front')
				->setMedia('screen,projection,tv');
		return $css;
	}

	/** @return CssLoader */
	protected function createComponentCssAdmin()
	{
		$css = $this->webLoader->createCssLoader('admin')
				->setMedia('screen,projection,tv');
		return $css;
	}

	/** @return CssLoader */
	protected function createComponentCssPrint()
	{
		$css = $this->webLoader->createCssLoader('print')
				->setMedia('print');
		return $css;
	}

	// </editor-fold>
}
