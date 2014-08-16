<?php

Namespace App\Components\Auth;

use Nette\Security as NS,
    Nette\Application\UI\Control,
    Nette\Application\UI\Form,
    Nette, Model;


use Kdyby\Facebook\Facebook;
use Kdyby\Facebook\Dialog\LoginDialog;
use Kdyby\Facebook\FacebookApiException;


/**
 * Sign in form control
 * @author Martin Šifra <me@martinsifra.cz>
 */
class FacebookControl extends Control
{

	/** @var Facebook */
	private $facebook;

	/** @var \App\Model\Facade\Registration */
	public $facade;

	/** @var \App\Model\Storage\RegistrationStorage */
	public $storage;

	
	public function __construct(Facebook $fb, \App\Model\Facade\Registration $facade, \App\Model\Storage\RegistrationStorage $storage)
	{
		parent::__construct();
		$this->facebook = $fb;
		$this->facade = $facade;
		$this->storage = $$storage;
	}

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/render.latte');
        $template->render();
    }

    public function renderIcon()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/renderIcon.latte');
        $template->render();
    }

	/** @return LoginDialog */
    protected function createComponentFbLogin()
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
                $me = $fb->api('/me');

                if (!$existing = $this->facade->findByFacebookId($fb->getUser())) {
					// Registration or merging process
					$this->storage->wipe();
					$this->storage->storeFromFacebook($fb->getUser(), $me, $fb->getAccessToken());

					if (($user = $this->facade->findByEmail($me->email))) {
						// Merge
//						$this->presenter->redirect('Sign:Merge');
						
						$this->facade->mergeFromFacebook($fb->getUser(), $me, $user, $fb->getAccessToken());
						
					} else {
						// Register
						$this->presenter->redirect('Sign:Register');
					}
				} else {
					// Login process
					$this->facade->updateFacebookAccessToken($fb->getUser(), $fb->getAccessToken());

					$this->presenter->user->login(new \Nette\Security\Identity($existing->id, $existing->getRolesArray(), $existing->toArray()));
				}
            } catch (FacebookApiException $e) {
                \Tracy\Debugger::log($e->getMessage(), 'facebook');
                $this->presenter->flashMessage("We are sorry, facebook authentication failed hard.");
            }

            $this->presenter->redirect('Sign:Register'); // Wherever I want, optimal is backlink to original request
        };

        return $dialog;
    }

}


interface IFacebookControlFactory
{
    /** @return FacebookControl */
    function create();
}
