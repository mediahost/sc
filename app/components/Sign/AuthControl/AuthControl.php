<?php

namespace App\components\Sign;

use Nette\Application\UI\Control,
	App\Model\Storage\RegistrationStorage as Storage,
	App\Model\Facade\Registration as Facade;

use Kdyby\Facebook\Facebook,
	Kdyby\Facebook\Dialog\LoginDialog,
	Kdyby\Facebook\FacebookApiException;

use Netrium\Addons\Twitter\Authenticator as Twitter,
	Netrium\Addons\Twitter\AuthenticationException as TwitterException;

/**
 * Description of AuthControl
 *
 * @author Martin Šifra <me@martinsifra.cz>
 */
class AuthControl extends Control
{
	
	/** @var Storage */
	private $storage;
	
	/** @var Facade */
	private $facade;
	
	/** @var Facebook */
	private $facebook;
	
	/** @var Twitter */
    private $twitter;

	
	public function __construct(Facade $facade, Storage $storage, Facebook $facebook, Twitter $twitter)
	{
		parent::__construct();
		$this->storage = $storage;
		$this->facade = $facade;		
		$this->facebook = $facebook;
		$this->twitter = $twitter;
	}

	/** @return LoginDialog */
	protected function createComponentFacebook()
	{
		$dialog = $this->facebook->createDialog('login');
		
		/** @var LoginDialog $dialog */
		$dialog->onResponse[] = function (LoginDialog $dialog) {
			$fb = $dialog->getFacebook();

			if (!$fb->getUser()) {
				$this->presenter->flashMessage("We are sorry, facebook authentication failed.");
				return;
			}

			try {
				$data = $fb->api('/me');
				$data->accessToken = $fb->getAccessToken();
				$source = 'facebook';
				
			} catch (FacebookApiException $e) {
				\Tracy\Debugger::log($e->getMessage(), 'facebook');
				
				$this->presenter->flashMessage("We are sorry, facebook authentication failed hard.");
			}
		};
		
		return $dialog;
	}
	
	/**
	 * 
	 * @throws NS\AuthenticationException
	 */
	public function handleTwitter()
	{
        try {
            $data = $this->twitter->tryAuthenticate();
			$source = 'twitter';
			
        } catch (TwitterException $e) {
			\Tracy\Debugger::log($e->getMessage(), 'twitter');
			
            throw new NS\AuthenticationException('Twitter authentication did not approve', self::NOT_APPROVED, $e);
        }
	}
	
	/**
	 * 
	 * @param type $source
	 * @param type $data
	 */
	private function process($source, $data)
	{
		if (!$existing = $this->facade->findByFacebookId($fb->getUser())) {
			
		} else {
			
		}	
	}
	
}


interface IAuthControlFactory
{
	/** @return AuthControl */
	function create();
}

