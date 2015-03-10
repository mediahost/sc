<?php

namespace App\Extensions\Settings\Model\Service;

use App\Extensions\Settings\Model\Storage\DefaultSettingsStorage;
use App\Model\Entity\PageConfigSettings;
use App\Model\Entity\PageDesignSettings;
use App\Model\Entity\User;
use Exception;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

/**
 * @property-read User $user
 */
abstract class BaseService extends Object
{

	/** @var DefaultSettingsStorage @inject */
	public $defaultStorage;

	/** @var EntityManager @inject */
	public $em;

	/** @return User|NULL */
	public function getUser()
	{
		if ($this->defaultStorage->loggedIn) {
			return $this->defaultStorage->user;
		}
		return NULL;
	}

	public function saveUser()
	{
		if ($this->user instanceof User && $this->user->id) {
			$userDao = $this->em->getDao(User::getClassName());
			if ($this->user->pageConfigSettings) {
				$configDao = $this->em->getDao(PageConfigSettings::getClassName());
				$configDao->save($this->user->pageConfigSettings);
			}
			if ($this->user->pageDesignSettings) {
				$designDao = $this->em->getDao(PageDesignSettings::getClassName());
				$designDao->save($this->user->pageDesignSettings);
			}
			return $userDao->save($this->user);
		} else {
			throw new BaseServiceException('User for saving must already exists');
		}
	}

}

class BaseServiceException extends Exception
{
	
}
