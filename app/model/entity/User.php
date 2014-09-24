<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection;

/**
 * User entity
 * @ORM\Entity
 *
 * @property string $mail
 * @property string $name
 * @property ArrayCollection $auths
 * @property ArrayCollection $roles
 * @property string $recoveryToken
 * @property \DateTime $recoveryExpiration
 */
class User extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $mail;

	/**
	 * @ORM\OneToMany(targetEntity="Auth", mappedBy="user", cascade={"persist","remove"})
	 * */
	protected $auths;

	/**
	 * @ORM\ManyToMany(targetEntity="Role", fetch="EAGER")
	 */
	protected $roles;
	
    /**
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    protected $name;
	
    /**
	 * @ORM\OneToOne(targetEntity="UserSettings", orphanRemoval=true)
	 */
    protected $settings = NULL;
	
	/**
	 * @ORM\Column(type="string", length=256, nullable=true)
	 */
	protected $recoveryToken;
	
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
	protected $recoveryExpiration;
	
	
	public function __construct()
	{
		$this->auths = new ArrayCollection();
		$this->roles = new ArrayCollection();
	}
	
	/**
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->mail;
	}
	
	/**
	 * @param Auth $auth
	 * @return User
	 */
	public function addAuth(Auth $auth)
	{
		$this->auths->add($auth);
		$auth->user = $this;
		return $this;
	}

	/**
	 * @param Role|array $role
	 * @param bool $clear Clear all previous roles.
	 * @return User
	 */
	public function addRole($role, $clear = FALSE)
	{
		if ($clear) {
			$this->clearRoles();
		}

		if (is_array($role)) {
			foreach ($role as $entity) {
				if (!$this->roles->contains($entity)) {
					$this->roles->add($entity);
				}
			}
		} else {
			if (!$this->roles->contains($role)) {
				$this->roles->add($role);
			}
		}
		
		return $this;
	}

	/**
	 * @return User
	 */
	public function clearRoles()
	{
		$this->roles->clear();
		return $this;
	}

	/**
	 * @return array
	 */
	public function getRolesKeys()
	{
		$array = [];
		foreach ($this->roles as $role) {
			$array[] = $role->id;
		}
		return $array;
	}
	
	/**
	 * @return array
	 */
	public function getRolesPairs()
	{
		$array = [];
		foreach ($this->roles as $role) {
			$array[$role->id] = $role->name;
		}
		return $array;
	}

	/**
	 * @param Role $role
	 * @return User
	 */
	public function removeRole(Role $role)
	{
		$this->roles->removeElement($role);
		return $this;
	}
	
	/**
	 * Set token and expiration DateTime.
	 * @param string $token
	 * @param \DateTime|string $expiration
	 * @return User
	 */
	public function setRecovery($token, $expiration)
	{
		if (!($expiration instanceof \DateTime)) {
			$expiration = new \DateTime($expiration);
		}
		
		$this->recoveryToken = $token;
		$this->recoveryExpiration = $expiration;
		
		return $this;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return [
			'id' => $this->id,
			'mail' => $this->mail,
			'name' => $this->name,
			'role' => $this->roles->toArray()
		];
	}
	
	/**
	 * Set NULL to recovery token and expiration properties.
	 * @return User
	 */
	public function unsetRecovery()
	{
		$this->recoveryToken = NULL;
		$this->recoveryExpiration = NULL;
		return $this;
	}

}
