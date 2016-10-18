<?php

namespace App\BaseModule\Presenters;

use App\Components\Auth\ISignOutFactory;
use App\Components\Auth\SignOut;
use App\Extensions\Settings\SettingsStorage;
use App\Model\Entity;
use App\Model\Facade\UserFacade;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\MemberAccessException as DoctrineMemberAccessException;
use Kdyby\Translation\Translator;
use Latte\Macros\MacroSet;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Presenter;
use Nette\Http\Request;
use Nette\MemberAccessException as NetteMemberAccessException;
use Nette\Security\IUserStorage;
use WebLoader\Nette\CssLoader;
use WebLoader\Nette\LoaderFactory;

abstract class BasePresenter extends Presenter
{

	const ACCESS_ID_PARAM = 'accessId';

	/** @persistent */
	public $lang;

	/** @persistent */
	public $backlink = '';

	// <editor-fold desc="injects">

	/** @var LoaderFactory @inject */
	public $webLoader;

	/** @var ISignOutFactory @inject */
	public $iSignOutFactory;

	/** @var Translator @inject */
	public $translator;

	/** @var SettingsStorage @inject */
	public $settings;

	/** @var EntityManager @inject */
	public $em;

	/** @var UserFacade @inject */
	public $userFacade;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->setLocale();
	}

	protected function beforeRender()
	{
		$this->template->setTranslator($this->translator);
		$this->template->lang = $this->translator->getLocale(); // TODO: remove lang from latte
		$this->template->locale = $this->translator->getLocale();
		$this->template->defaultLocale = $this->translator->getDefaultLocale();
		$this->template->allowedLanguages = $this->translator->getAvailableLocales();

		$this->template->identity = $this->getUser()->getIdentity();
		$this->template->pageInfo = $this->settings->getPageInfo();

		$this->template->isCandidate = in_array(Entity\Role::CANDIDATE, $this->getUser()->getRoles());
		$this->template->isCompany = in_array(Entity\Role::COMPANY, $this->getUser()->getRoles());
		$this->template->isAdmin = in_array(Entity\Role::ADMIN, $this->getUser()->getRoles()) || in_array(Entity\Role::SUPERADMIN, $this->getUser()->getRoles());
	}
	// <editor-fold desc="requirments">

	public function checkRequirements($element)
	{
		$secured = $element->getAnnotation('secured');
		$resource = $element->getAnnotation('resource');
		$privilege = $element->getAnnotation('privilege');
		$companySecured = $element->getAnnotation('companySecured');
		$companyResource = $element->getAnnotation('companyResource');
		$companyPrivilege = $element->getAnnotation('companyPrivilege');

		if ($secured) {
			$this->checkSecured($resource, $privilege);
		}
		if ($companySecured) {
			$this->checkCompanySecured($companyResource, $companyPrivilege);
		}
	}

	private function checkSecured($resource, $privilege)
	{
		if (!$this->user->loggedIn) {
			if ($this->getParameter('accessId')) {
				$this->redirect(':Front:Sign:access', [
					'token' => $this->getParameter(self::ACCESS_ID_PARAM),
					'backlink' => $this->storeRequest(),
				]);
			}
			if ($this->user->logoutReason === IUserStorage::INACTIVITY) { // can redirect to lock screen
				$message = $this->translator->translate('You have been signed out, because you have been inactive for long time.');
				$this->flashMessage($message);
				$this->redirect(':Front:Sign:in', ['backlink' => $this->storeRequest()]);
			} else {
				$message = $this->translator->translate('You should be logged in!');
				$this->flashMessage($message);
				$this->redirect(':Front:Sign:in', ['backlink' => $this->storeRequest()]);
			}
		} elseif (!$this->user->isAllowed($resource, $privilege)) {
			throw new ForbiddenRequestException;
		}
	}

	public function restoreRequest($key)
	{
		$session = $this->getSession('Nette.Application/requests');
		/** @var Request $request */
		$request = $session[$key][1];
		if ($request && $request instanceof Request) {
			$params = $request->getParameters();
			unset($params[self::ACCESS_ID_PARAM]);
			$request->setParameters($params);
		}
		return parent::restoreRequest($key);
	}

	private function checkCompanySecured($resource, $privilege)
	{
		try {
			if ($this->companyPermission === NULL) {
				$className = $this->getReflection()->getName();
				$exceptionMessage = 'Variable ' . $className . '::$companyPermission mut be instance of ' . Entity\CompanyPermission::getClassName();
				throw new ForbiddenRequestException($exceptionMessage);
			}
			if (!$this->companyPermission->isAllowed($resource, $privilege)) {
				throw new ForbiddenRequestException;
			}
		} catch (NetteMemberAccessException $e) {
			$className = $this->getReflection()->getName();
			$exceptionMessage = 'Must set ' . $className . '::$companyPermission before use @companySecured annotations.';
			$exceptionMessage .= ' Define it in ' . $className . '::startup().';
			throw new ForbiddenRequestException($exceptionMessage);
		} catch (DoctrineMemberAccessException $e) {
			$className = $this->getReflection()->getName();
			$exceptionMessage = 'Variable ' . $className . '::$companyPermission mut be instance of ' . Entity\CompanyPermission::getClassName();
			throw new ForbiddenRequestException($exceptionMessage);
		}
	}

	// </editor-fold>
	// <editor-fold desc="language">

	private function setLocale()
	{

	}

	// </editor-fold>
	// <editor-fold desc="handlers">

	public function handleChangeLanguage($locale)
	{
		if (in_array($locale, $this->translator->getAvailableLocales())) {
			$this->redirect('this', ['lang' => $locale]);
		} else {
			$message = $this->translator->translate('Requested language isn\'t supported.');
			$this->flashMessage($message, 'warning');
			$this->redirect('this');
		}
	}

	// </editor-fold>
	// <editor-fold desc="components">

	/** @return SignOut */
	public function createComponentSignOut()
	{
		return $this->iSignOutFactory->create();
	}

	// </editor-fold>
	// <editor-fold desc="css webloader">

	/** @return CssLoader */
	protected function createComponentCssFront()
	{
		$css = $this->webLoader->createCssLoader('front');
		return $css;
	}

	/** @return CssLoader */
	protected function createComponentCssApp()
	{
		$css = $this->webLoader->createCssLoader('app');
		return $css;
	}

	/** @return CssLoader */
	protected function createComponentCssSupr()
	{
		$css = $this->webLoader->createCssLoader('supr');
		return $css;
	}

	/** @return CssLoader */
	protected function createComponentCssPrint()
	{
		$css = $this->webLoader->createCssLoader('print')
				->setMedia('print');
		return $css;
	}

	public function createComponentJsSuprCore()
	{
		$js = $this->webLoader->createJavaScriptLoader('suprCore');
		return $js;
	}

	public function createComponentJsLibs()
	{
		$js = $this->webLoader->createJavaScriptLoader('libs');
		return $js;
	}

	public function createComponentJsCustomScripts()
	{
		$js = $this->webLoader->createJavaScriptLoader('customScripts');
		return $js;
	}

	// </editor-fold>
	// <editor-fold desc="macros">
	protected function createTemplate()
	{
		$template = parent::createTemplate();
		$latte = $template->getLatte();

		$set = new MacroSet($latte->getCompiler());
		$set->addMacro('scache', '?>?<?php echo strtotime(date(\'Y-m-d hh \')); ?>"<?php');

		$latte->addFilter('scache', $set);
		return $template;
	}

	// </editor-fold>
}
