<?php

namespace App\Model\Entity;

use App\Model\Entity\Traits\AdminJobs;
use App\Model\Entity\Traits\IUserSocials;
use App\Model\Entity\Traits\UserPassword;
use App\Model\Entity\Traits\UserRoles;
use App\Model\Entity\Traits\UserSocials;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Security\IIdentity;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\UserRepository")
 *
 * @property string $mail
 * @method self setMail(string $mail)
 * @property bool|null $beNotified
 * @property bool $verificated
 */
class User extends BaseEntity implements IIdentity, IUserSocials
{
	use Identifier;
	use UserRoles;
	use UserPassword;
	use UserSocials;

	/** @ORM\Column(type="string", nullable=false, unique=true) */
	protected $mail;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $beNotified;

	public function __construct($mail = NULL, $verificated = FALSE)
	{
		$this->roles = new ArrayCollection();
		$this->allowedCompanies = new ArrayCollection();
		$this->verificated = $verificated;
		if ($mail) {
			$this->mail = $mail;
		}
		parent::__construct();
	}

	public function __toString()
	{
		return (string)$this->mail;
	}

	public function toArray()
	{
		return [
			'id' => $this->id,
			'mail' => $this->mail,
			'role' => $this->roles->toArray(),
		];
	}

	public function getData()
	{
		return $this->toArray();
	}

	public function isNew()
	{
		return $this->id === NULL;
	}

}
