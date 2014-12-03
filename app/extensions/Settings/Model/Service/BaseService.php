<?php

namespace App\Extensions\Settings\Model\Service;

use App\Extensions\Settings\Model\Storage\DefaultSettingsStorage;
use App\Model\Entity;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;
use Nette\Security;

/**
 * BaseService
 * 
 * @author Petr Poupě <petr.poupe@gmail.com>
 * 
 * @property-write Security\User $user
 * @property-read Entity\User $user
 */
abstract class BaseService extends Object
{

	/** @var DefaultSettingsStorage @inject */
	public $defaultStorage;

	/** @var EntityManager @inject */
	public $em;

	/** @var Entity\User */
	private $user;

	/** @return Entity\User */
	public function getUser()
	{
		if ($this->defaultStorage->identity && $this->defaultStorage->identity->loggedIn && $this->defaultStorage->identity->id) {
			$userDao = $this->em->getDao(Entity\User::getClassName());
			$this->user = $userDao->find($this->defaultStorage->identity->id);
		}
		return $this->user;
	}

}
