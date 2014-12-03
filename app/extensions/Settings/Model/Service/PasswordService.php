<?php

namespace App\Extensions\Settings\Model\Service;

/**
 * PasswordService
 * 
 * @author Petr Poupě <petr.poupe@gmail.com>
 * 
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
