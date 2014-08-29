<?php

Namespace App\Components\Auth;

use Nette\Security as NS,
	Nette\Application\UI\Control,
	Nette\Application\UI\Form,
	Nette,
	Model;

/**
 * Sign in form control
 * @author Martin Šifra <me@martinsifra.cz>
 */
class TwitterControl extends Control
{

	/** @var Facebook */
	private $facebook;

	/** @var \App\Model\Facade\RegistrationFacade */
	public $facade;

	/** @var \App\Model\Storage\RegistrationStorage */
	public $storage;
	
    /** @var \Netrium\Addons\Twitter\Authenticator */
    protected $authenticator;

	public function __construct(\Netrium\Addons\Twitter\Authenticator $twitter, \App\Model\Facade\RegistrationFacade $facade, \App\Model\Storage\RegistrationStorage $storage)
	{
//		parent::__construct();
		$this->authenticator = $twitter;
		$this->facade = $facade;
		$this->storage = $storage;
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
	
	public function handleLogin()
	{
        try {
            $data = $this->authenticator->tryAuthenticate();
			
//			dump($data);
//			exit();
			
        } catch (Netrium\Addons\Twitter\AuthenticationException $e) {
            throw new NS\AuthenticationException('Twitter authentication did not approve', self::NOT_APPROVED, $e);
        }
		
		$auth = $this->storage->storeFromTwitter(Nette\Utils\ArrayHash::from($data['user']));
		
		if (!$existing = $this->facade->findByTwitterId($auth->key)) {
			if ($this->storage->checkRequired()) { // Mám všechny povinné údaje pro registraci?
				if (($user = $this->facade->findByEmail($this->storage->data->email))) { // E-mail nemusím vždy dostat!
					// Merge
					$this->facade->merge($user, $auth);
				} else {
					// Register
					$user = $this->facade->merge($this->storage->user, $this->storage->auth);
				}
				
				$this->presenter->user->login(new \Nette\Security\Identity($user->id, $user->getRolesPairs(), $user->toArray()));
				$this->presenter->redirect(':Admin:Dashboard:');
			} else {
				$this->presenter->redirect('Sign:Register');
			}
		} else {
			$this->presenter->user->login(new \Nette\Security\Identity($existing->id, $existing->getRolesPairs(), $existing->toArray()));
			$this->presenter->redirect(':Admin:Dashboard:');
		}
	}
}

interface ITwitterControlFactory
{
	/** @return TwitterControl */
	function create();
}
