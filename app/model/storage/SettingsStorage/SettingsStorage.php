<?php

namespace App\Model\Storage;

use Nette\Object;
use Nette\Utils\ArrayHash;

/**
 * @author Martin Šifra <me@martinsifra.cz>
 */
class SettingsStorage extends Object
{
	/** @var ArrayHash $expiration */
	private $expiration;
	
	public function setExpiration(array $expiration)
	{
		$this->expiration = ArrayHash::from($expiration);
	}
	
	public function getExpiration()
	{
		return $this->expiration;
	}

}
