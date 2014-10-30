<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\MappedSuperclass
 */
class OAuth extends BaseEntity
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="string", length=255)
	 * @var string
	 */
	protected $id;

}
