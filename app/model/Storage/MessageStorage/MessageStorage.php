<?php

namespace App\Model\Storage;

/**
 * Description of MessageStorage
 *
 * @author Martin Šifra <me@martinsifra.cz>
 */
class MessageStorage extends \Nette\Object
{

	public function getTemplate($filename, $params)
	{
		$latte = new Latte\Engine;
		return $latte->renderToString(__DIR__ . $filename . '.latte', $params);
	}

}
