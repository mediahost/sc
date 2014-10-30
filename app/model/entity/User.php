<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection,
	Nette\Security\Passwords;

/**
 * User entity
 * @ORM\Entity
 *
 * @property string $mail
 * @property string $name
 * @property ArrayCollection $auths
 * @property ArrayCollection $roles
 * @property UserSettings $settings
 * @property string $recoveryToken
 * @property \DateTime $recoveryExpiration
 */
class User extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	// <editor-fold defaultstate="collapsed" desc="constants & variables">

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
	 * @ORM\OneToOne(targetEntity="UserSettings", mappedBy="user", orphanRemoval=true, fetch="LAZY", cascade={"all"})
	 */
	protected $settings;

    /**
     * @ORM\OneToOne(targetEntity="Facebook", orphanRemoval=true, fetch="LAZY", cascade={"all"})
     * @ORM\JoinColumn(name="facebook_id", referencedColumnName="id")
     **/
	protected $facebook;

    /**
     * @ORM\OneToOne(targetEntity="Twitter", orphanRemoval=true, fetch="LAZY", cascade={"all"})
     * @ORM\JoinColumn(name="twitter_id", referencedColumnName="id")
     **/
	protected $twitter;
	
	/**
	 * @ORM\Column(type="string", length=256, nullable=true)
	 */
	protected $hash;
	
	/**
	 * @ORM\Column(type="string", length=256, nullable=true)
	 */
	protected $recoveryToken;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $recoveryExpiration;

	// </editor-fold>

	public function __construct()
	{
		$this->auths = new ArrayCollection();
		$this->roles = new ArrayCollection();
	}

	// <editor-fold defaultstate="collapsed" desc="setters">

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
	 * Computes salted password hash.
	 * @param string Password to be hashed.
	 * @param array with cost (4-31), salt (22 chars)
	 * @return Auth
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

	/**
	 * @return User
	 */
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
	 * @param UserSettings $settings
	 */
	public function setSettings(UserSettings $settings)
	{
		$settings->user = $this;
		$this->settings = $settings;
		return $this;
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

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">

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

	// </editor-fold>

	/**
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->mail;
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

}
