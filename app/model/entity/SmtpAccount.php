<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property string $host
 * @property string $username
 * @property string $password
 * @property bool $secure
 */
class SmtpAccount extends BaseEntity
{

	use Identifier;

	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $host;

	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $username;

	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $password;

	/** @ORM\Column(type="boolean") */
	protected $secure = FALSE;

	public function __toString()
	{
		return $this->username . '@' . $this->host;
	}

}
