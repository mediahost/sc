<?php

namespace App\Listeners;

use App\Mail\Messages\VerificationMessage;
use App\Model\Entity\SignUp;
use App\Model\Entity\User;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use App\Model\Storage\MessageStorage;
use App\Model\Storage\SignUpStorage;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Events\Subscriber;
use Nette\Application\Application;
use Nette\Application\UI\Control;
use Nette\Latte;
use Nette\Mail\IMailer;
use Nette\Object;
use Nette\Security\Identity;
use Tracy\Debugger;

class SignListener extends Object implements Subscriber
{
	
	const REDIRECT_AFTER_SIGNIN = ':App:Dashboard:';
	const REDIRECT_SIGNIN_PAGE = ':Front:Sign:in';
	
	/** @var SignUpStorage @inject */
	public $session;
	
	/** @var UserFacade @inject */
	public $userFacade;
	
	/** @var RoleFacade @inject */
	public $roleFacade;
	
	/** @var IMailer @inject */
	public $mailer;
	
	/** @var Application @inject */
	public $application;

	public function __construct(Application $application)
	{
		$this->application = $application->presenter;
	}

	public function getSubscribedEvents()
	{
		return array(
			'App\Components\Profile\FacebookControl::onSuccess' => 'onStartup',
			'App\Components\Profile\SignUpControl::onSuccess' => 'onStartup',
			'App\Components\Profile\TwitterControl::onSuccess' => 'onStartup',
			'App\Components\Profile\RequiredControl::onSuccess' => 'onExists',
			'App\Components\Profile\SummaryControl::onSuccess' => 'onVerify',
			'App\FrontModule\Presenters\SignPresenter::onVerify' => 'onSuccess'
		);
	}

	public function onStartup(Control $control, User $user)
	{
		if ($user->id) {
			$control->presenter->user->login(new Identity($user->id, $user->getRolesPairs(), $user->toArray()));
			$control->presenter->redirect(self::REDIRECT_AFTER_SIGNIN);
		} else {
			$this->session->user = $user;
			$this->onRequire($control, $user);
		}
	}
	
	public function onRequire(Control $control, User $user)
	{
		if (!$user->mail) {
			$control->presenter->redirect(':Front:Sign:up', [
				'step' => 'required']
			);
		} else {
			$this->onExists($control, $user);
		}	
	}
	
	public function onExists(Control $control, User $user)
	{
		if (!$existing = $this->userFacade->findByMail($user->mail)) {
			$control->presenter->redirect(':Front:Sign:up', [
				'step' => 'additional'
			]);	
		} else {
			$control->presenter->flash('This e-mail is registered yet.');
			$control->presenter->redirect(self::REDIRECT_SIGNIN_PAGE);
		}
	}
	
	public function onVerify(Control $control, User $user)
	{
		if (!$this->session->isVerified()) {
			$role = $this->roleFacade->findByName($this->session->role);
			
			// Sign up temporarily
			$signUp = new SignUp();
			$signUp->setMail($user->mail)
					->setHash($user->hash)
					->setName($user->name)
					->setRole($role);

			if ($user->facebook) {
				$signUp->setFacebookId($user->facebook->id)
					->setFacebookAccessToken($user->facebook->accessToken);
			}
			
			if ($user->twitter) {
				$signUp->setTwitterId($user->twitter->id)
					->setTwitterAccessToken($user->twitter->accessToken);
			}

			$signUp = $this->userFacade->signUpTemporarily($signUp);
			
			// Send verification e-mail
			$latte = new Latte\Engine;
			$params = ['link' => $this->application->presenter->link('//:Front:Sign:verify', $signUp->verificationToken)];
			$message = new VerificationMessage();
			$message->addTo($user->mail)
					->setHtmlBody($latte->renderToString($message->getPath(), $params));
			
			$this->mailer->send($message);
			
			$control->presenter->flashMessage('We have sent you a verification e-mail. Please check your inbox!', 'success');
			$control->presenter->redirect(self::REDIRECT_SIGNIN_PAGE);
		} else {
			$this->onSuccess($control, $user);
		}
	}
	
	
	public function onSuccess(Control $control, User $user)
	{
		Debugger::dump('...');exit;
		if ($existing = $this->userFacade->findByMail($user->mail)) {
			$control->presenter->flash('This e-mail is registered yet.');
			$control->presenter->redirect(self::REDIRECT_SIGNIN_PAGE);
		} else {
			if (empty($user->roles)) {
				$user->addRole($this->roleFacade->findByName($this->session->role));
			}
			$user = $this->userFacade->signUp($user);
		}
		
		$this->signIn($control, $user);
	}

	protected function signIn(Control $control, User $user)
	{
		$control->presenter->user->login(new Identity($user->id, $user->getRolesPairs(), $user->toArray()));
		$control->presenter->restoreRequest($control->presenter->backlink);
		$control->presenter->redirect(self::REDIRECT_AFTER_SIGNIN);
	}

}
