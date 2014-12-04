<?php

namespace App\Extensions\Settings\Model\Service;

use App\Extensions\Settings\Model\Storage\DefaultSettingsStorage;
use App\Model\Entity\User;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

/**
 * BaseService
 * 
 * @author Petr Poupě <petr.poupe@gmail.com>
 * 
 * @property-read User $user
 */
abstract class BaseService extends Object
{

	/** @var DefaultSettingsStorage @inject */
	public $defaultStorage;

	/** @var EntityManager @inject */
	public $em;

	/** @return User */
	public function getUser()
	{
		return $this->defaultStorage->user;
	}

}
