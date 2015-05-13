<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 * @property User $user
 * @property Company $company
 * @property Communication $communication
 */
class Sender extends BaseEntity
{

	use Identifier;

	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 * @var User
	 */
	protected $user;

	/**
	 * @ORM\ManyToOne(targetEntity="Company")
	 * @var Company
	 */
	protected $company;

	/**
	 * @ORM\ManyToOne(targetEntity="Communication", inversedBy="contributors")
	 * @var Communication
	 */
	protected $communication;

	/**
	 * TODO: implement
	 */
	public function getImage()
	{
		
	}

	public function getName()
	{
		if ($this->company) {
		    return $this->company->name;
		} elseif ($this->user->candidate) {
			return $this->user->candidate->name;
		} else {
			return 'no name';
		}
	}

	/**
	 * TODO: implement - vrací odkaz na veřejný profil společnosti nebo uživatele (veřejný profil uživatele, zatím neexistuje, takže bude stačit, když bude vracet odkaz na editaci uživatele
	 */
	public function getPublicLink()
	{

	}

}