<?php

namespace App\Model\Facade;

class Auths extends Base
{
	public function findByEmail($email)
	{
		return $this->dao->findOneBy([
			'source' => 'app',
			'key' => $email
		]);
	}
}
