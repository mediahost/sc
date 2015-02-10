<?php

namespace App\Extensions\Settings\Model\Service;

/**
 * @property-read string $length Length of password
 */
class PasswordService extends BaseService
{

	/**
	 * @return int 
	 */
	public function getLength()
	{
		return (int) $this->defaultStorage->passwords->length;
	}

}
