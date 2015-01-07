<?php

namespace App\Model\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;

/**
 * @ORM\Entity
 *
 * @property string $mail
 * @property string $hash
 * @property-write $password
 * @property-read array $roles
 * @property-read array $rolesKeys
 * @property Role $maxRole
 * @property PageConfigSettings $pageConfigSettings
 * @property PageDesignSettings $pageDesignSettings
 * @property Facebook $facebook
 * @property Twitter $twitter
 * @property Candidate $candidate
 * @property string $socialName
 * @property string $socialBirthday
 * @property int $connectionCount
 * @property string $recoveryToken
 * @property DateTime $recoveryExpiration
 * @property Role $requiredRole
 * @method self setMail(string $mail)
 * @method self setRequiredRole(Role $role)
 */
class User extends BaseEntity implements IIdentity
{

	const SOCIAL_CONNECTION_APP = 'app';
	const SOCIAL_CONNECTION_FACEBOOK = 'facebook';
	const SOCIAL_CONNECTION_TWITTER = 'twitter';
	const SOCIAL_CONNECTION_GOOGLE = 'google';
	const SOCIAL_CONNECTION_GITHUB = 'github';
	const SOCIAL_CONNECTION_LINKEDIN = 'linkedin';

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/** @ORM\Column(type="string", nullable=false, unique=true) */
	protected $mail;

	/** @ORM\ManyToMany(targetEntity="Role", fetch="EAGER", cascade={"persist"}) */
	private $roles;

	/** @ORM\OneToOne(targetEntity="PageConfigSettings", fetch="EAGER", cascade={"persist", "remove"}) */
	protected $pageConfigSettings;

	/** @ORM\OneToOne(targetEntity="PageDesignSettings", fetch="EAGER", cascade={"persist", "remove"}) */
	protected $pageDesignSettings;

	/** @ORM\OneToOne(targetEntity="Facebook", fetch="LAZY", cascade={"persist", "remove"}) */
	protected $facebook;

	/** @ORM\OneToOne(targetEntity="Twitter", fetch="LAZY", cascade={"persist", "remove"}) */
	protected $twitter;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $hash;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $recoveryToken;

	/** @ORM\Column(type="datetime", nullable=true) */
	protected $recoveryExpiration;

	/**
	 * @ORM\ManyToOne(targetEntity="Role", fetch="LAZY")
	 * @ORM\JoinColumn(name="required_role_id", referencedColumnName="id", nullable=true)
	 */
	protected $requiredRole;

	/** @ORM\OneToOne(targetEntity="Candidate", fetch="LAZY", cascade={"persist"}) */
	protected $candidate;
	
	/** @ORM\OneToMany(targetEntity="CompanyPermission", mappedBy="user", fetch="LAZY", cascade={"persist"}) */
	protected $allowedCompanies;

	public function __construct()
	{
		parent::__construct();
		$this->roles = new ArrayCollection;
		$this->allowedCompanies = new ArrayCollection;
	}

	// <editor-fold defaultstate="collapsed" desc="setters">

	/**
	 * Computes salted password hash.
	 * @param string Password to be hashed.
	 * @param array with cost (4-31), salt (22 chars)
	 * @return self
	 */
	public function setPassword($password, array $options = NULL)
	{
		$this->hash = Passwords::hash($password, $options);
		return $this;
	}

	/**
	 * Removes App login password
	 * @return self
	 */
	public function clearHash()
	{
		$this->hash = NULL;
		return $this;
	}

	/**
	 * Removes social auth
	 * @return self
	 */
	public function clearFacebook()
	{
		$this->facebook = NULL;
		return $this;
	}

	/**
	 * Removes social auth
	 * @return self
	 */
	public function clearTwitter()
	{
		$this->twitter = NULL;
		return $this;
	}

	/**
	 * @param Role|array $role
	 * @param bool $clear Clear all previous roles.
	 * @return self
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

	/** @return self */
	public function clearRoles()
	{
		$this->roles->clear();
		return $this;
	}

	/**
	 * @param Role $role
	 * @return self
	 */
	public function removeRole(Role $role)
	{
		$this->roles->removeElement($role);
		return $this;
	}

	/**
	 * Set recovery tokens
	 * @param string $token
	 * @param DateTime|string $expiration
	 * @return self
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

	/**
	 * Removes recovery tokens
	 * @return self 
	 */
	public function removeRecovery()
	{
		$this->recoveryToken = NULL;
		$this->recoveryExpiration = NULL;
		return $this;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">

	/**
	 * Return user name of social connection
	 * Prefer FB
	 * @return string
	 */
	public function getSocialName()
	{
		if ($this->facebook) {
			return $this->facebook->name;
		}
		if ($this->twitter) {
			return $this->twitter->name;
		}
		return NULL;
	}

	/**
	 * Return user birthday from social connections
	 * @return string
	 */
	public function getSocialBirthday()
	{
		if ($this->facebook) {
			return $this->facebook->birthday;
		}
		return NULL;
	}

	/**
	 * Decides if asked connection is defined
	 * @param type $socialName
	 * @return boolean
	 */
	public function hasSocialConnection($socialName)
	{
		switch ($socialName) {
			case self::SOCIAL_CONNECTION_APP:
				return (bool) $this->hash;
			case self::SOCIAL_CONNECTION_FACEBOOK:
				return (bool) ($this->facebook instanceof Facebook && $this->facebook->id);
			case self::SOCIAL_CONNECTION_TWITTER:
				return (bool) ($this->twitter instanceof Twitter && $this->twitter->id);
			default:
				return FALSE;
		}
	}

	/**
	 * Get count of all connections
	 * @return int
	 */
	public function getConnectionCount()
	{
		$allConnections = [
			self::SOCIAL_CONNECTION_APP,
			self::SOCIAL_CONNECTION_FACEBOOK,
			self::SOCIAL_CONNECTION_GITHUB,
			self::SOCIAL_CONNECTION_GOOGLE,
			self::SOCIAL_CONNECTION_LINKEDIN,
			self::SOCIAL_CONNECTION_TWITTER,
		];
		$count = 0;
		foreach ($allConnections as $connection) {
			if ($this->hasSocialConnection($connection)) {
				$count++;
			}
		}
		return $count;
	}

	/**
	 * Return array of roles ids
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
	 * Return array with roleID => roleName
	 * @return array 
	 */
	public function getRoles()
	{
		$array = [];
		foreach ($this->roles as $role) {
			if ($role->id) {
				$array[$role->id] = $role->name;
			} else {
				$array[] = $role->name;
			}
		}
		return $array;
	}

	/**
	 * Get max role of user roles
	 * @return Role
	 */
	public function getMaxRole()
	{
		return Role::getMaxRole($this->roles);
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
	 * Same as Passwords::needsRehash($this->hash, $options) 
	 * @param  array with cost (4-31)
	 * @return bool
	 */
	public function needsRehash(array $options = NULL)
	{
		return Passwords::needsRehash($this->hash, $options);
	}

	// </editor-fold>

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
			'role' => $this->roles->toArray(),
		];
	}

}
