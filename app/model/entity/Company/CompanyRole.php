<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property string $name
 */
class CompanyRole extends BaseEntity
{

	use Identifier;

	const EDITOR = 'editor';
	const MANAGER = 'manager';
	const ADMIN = 'admin';
	const MESSENGER = 'messenger';

	/** @ORM\Column(type="string", length=50) */
	protected $name;

	public function __construct($name = NULL)
	{
		parent::__construct();
		if ($name) {
			$this->name = $name;
		}
	}

	public function __toString()
	{
		return (string) $this->name;
	}

}
