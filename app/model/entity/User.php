<?php

namespace App\Model\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Security\Passwords;

/**
 * @ORM\Entity
 *
 * @property string $mail
 * @property string $name
 * @property string $hash
 * @property-write $password
 * @property ArrayCollection $roles
 * @property UserSettings $settings
 * @property Facebook $facebook
 * @property Twitter $twitter
 * @property string $recoveryToken
 * @property DateTime $recoveryExpiration
 */
class User extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/** @ORM\Column(type="string", nullable=false) */
	protected $mail;

	/** @ORM\ManyToMany(targetEntity="Role", fetch="EAGER", cascade={"persist"}) */
	protected $roles;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $name;

	/** @ORM\OneToOne(targetEntity="UserSettings", mappedBy="user", fetch="LAZY", cascade={"all"}, orphanRemoval=true) */
	protected $settings;

    /** @ORM\OneToOne(targetEntity="Facebook", mappedBy="user", fetch="LAZY", cascade={"all"}, orphanRemoval=true) */
	protected $facebook;

    /** @ORM\OneToOne(targetEntity="Twitter", mappedBy="user", fetch="LAZY", cascade={"all"}, orphanRemoval=true) */
	protected $twitter;
	
	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $hash;
	
	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $recoveryToken;

	/** @ORM\Column(type="datetime", nullable=true) */
	protected $recoveryExpiration;

	public function __construct()
	{
		$this->roles = new ArrayCollection();
	}

	/**
	 * Computes salted password hash.
	 * @param string Password to be hashed.
	 * @param array with cost (4-31), salt (22 chars)
	 * @return User
	 */
	public function setPassword($password, array $options = NULL)
	{
		$this->hash = Passwords::hash($password, $options);
		return $this;
	}
	
	/**
	 * Verifies that a password matches a hash.
	 * @param string $password Password in plain text
	 * @return bool
	 */
	public function verifyPassword($password)
	{
		return Passwords::verify($password, $this->hash);
	}

	/**
	 * Checks if the given hash matches the options.
	 * @param  array with cost (4-31)
	 * @return bool
	 */
	public function needsRehash(array $options = NULL)
	{
		return Passwords::needsRehash($this->hash, $options);
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

	/** @return User */
	public function clearRoles()
	{
		$this->roles->clear();
		return $this;
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
	 * @param string $token
	 * @param DateTime|string $expiration
	 * @return User
	 */
	public function setRecovery($token, $expiration)
	{
		if (!($expiration instanceof DateTime)) {
			$expiration = new DateTime($expiration);
		}

		$this->recoveryToken = $token;
		$this->recoveryExpiration = $expiration;

		return $this;
	}
	
	/** @return User */
	public function removeRecovery()
	{
		$this->recoveryToken = NULL;
		$this->recoveryExpiration = NULL;
		return $this;
	}

	/**
	 * @param UserSettings $settings
	 * @return User
	 */
	public function setSettings(UserSettings $settings)
	{
		$settings->user = $this;
		$this->settings = $settings;
		return $this;
	}

	/**
	 * @param Facebook $facebook
	 * @return User
	 */
	public function setFacebook(Facebook $facebook)
	{
		$facebook->user = $this;
		$this->facebook = $facebook;
		return $this;
	}
	
	/**
	 * @param Twitter $twitter
	 * @return User
	 */
	public function setTwitter(Twitter $twitter)
	{
		$twitter->user = $this;
		$this->twitter = $twitter;
		return $this;
	}
	
	/** @return array */
	public function getRolesKeys()
	{
		$array = [];
		foreach ($this->roles as $role) {
			$array[] = $role->id;
		}
		return $array;
	}

	/** @return array */
	public function getRolesPairs()
	{
		$array = [];
		foreach ($this->roles as $role) {
			$array[$role->id] = $role->name;
		}
		return $array;
	}

	/** @return string */
	public function __toString()
	{
		return (string) $this->mail;
	}

	/** @return array */
	public function toArray()
	{
		return [
			'id' => $this->id,
			'mail' => $this->mail,
			'name' => $this->name,
			'role' => $this->roles->toArray()
		];
	}

}
