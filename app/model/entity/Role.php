<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Role entity
 * @ORM\Entity
 *
 * @property string $name
 */
class Role extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	// <editor-fold defaultstate="collapsed" desc="constants & variables">

	const ROLE_GUEST = 'guest';
	const ROLE_SIGNED = 'signed';
	const ROLE_CANDIDATE = 'candidate';
	const ROLE_COMPANY_ADMIN = 'company_admin';

	/**
	 * @ORM\Column(type="string", length=128)
	 */
	protected $name;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="setters">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">
	// </editor-fold>
}
