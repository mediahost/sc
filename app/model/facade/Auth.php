<?php

namespace App\Model\Facade;

use Kdyby\Doctrine\EntityDao,
	App\Model\Entity;

class Auth extends Base
{
	/** @var EntityDao */
	private $auths;

	private function init()
	{
		$this->auths = $this->em->getDao(Entity\Auth::getClassName());
	}

	
	public function findByEmail($email)
	{
		return $this->auths->findOneBy([
			'source' => 'app',
			'key' => $email
		]);
	}
}
