<?php

namespace App\Mail\Messages;

use Nette\Application\UI\ITemplate;
use Nette\Mail\Message;

/**
 * @author Martin Šifra <me@martinsifra.cz>
 */
abstract class BaseMessage extends Message
{

	/**
	 * @return string
	 */
	public function getPath()
	{
		$name = $this->reflection->getShortName();
		return __DIR__ . '/' . $name . '/' . $name . '.latte';
	}

}
