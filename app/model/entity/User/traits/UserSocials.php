<?php

namespace App\Model\Entity\Traits;

use App\Model\Entity\Facebook;
use App\Model\Entity\Linkedin;
use App\Model\Entity\Twitter;

interface IUserSocials
{

	const SOCIAL_CONNECTION_APP = 'app';
	const SOCIAL_CONNECTION_FACEBOOK = 'facebook';
	const SOCIAL_CONNECTION_TWITTER = 'twitter';
	const SOCIAL_CONNECTION_GOOGLE = 'google';
	const SOCIAL_CONNECTION_GITHUB = 'github';
	const SOCIAL_CONNECTION_LINKEDIN = 'linkedin';

}

/**
 * @property Facebook $facebook
 * @property Twitter $twitter
 * @property Linkedin $linkedin
 * @property string $socialName
 * @property string $socialBirthday
 * @property int $connectionCount
 * @property string $facebookLink
 * @property string $twitterLink
 * @property string $googleLink
 * @property string $linkedinLink
 * @property string $pinterestLink
 */
trait UserSocials
{

	/** @ORM\OneToOne(targetEntity="Facebook", fetch="LAZY", cascade={"persist", "remove"}, orphanRemoval=true) */
	protected $facebook;

	/** @ORM\OneToOne(targetEntity="Twitter", fetch="LAZY", cascade={"persist", "remove"}, orphanRemoval=true) */
	protected $twitter;

	/** @ORM\OneToOne(targetEntity="Linkedin", fetch="LAZY", cascade={"persist", "remove"}, orphanRemoval=true) */
	protected $linkedin;
	
	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $facebookLink;
	
	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $twitterLink;
	
	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $googleLink;
	
	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $linkedinLink;
	
	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $pinterestLink;
	

	public function clearFacebook()
	{
		$this->facebook = NULL;
		return $this;
	}

	public function clearTwitter()
	{
		$this->twitter = NULL;
		return $this;
	}

	public function clearLinkedin()
	{
		$this->linkedin = NULL;
		return $this;
	}

	public function getSocialName()
	{
		if ($this->linkedin) { // prefer LinkedIn
			return $this->linkedin->name;
		}
		if ($this->facebook) {
			return $this->facebook->name;
		}
		if ($this->twitter) {
			return $this->twitter->name;
		}
		return NULL;
	}

	public function getSocialBirthday()
	{
		if ($this->facebook) {
			return $this->facebook->birthday;
		}
		return NULL;
	}

	public function hasSocialConnection($socialConnectionName)
	{
		switch ($socialConnectionName) {
			case IUserSocials::SOCIAL_CONNECTION_APP:
				return (bool) $this->hash;
			case IUserSocials::SOCIAL_CONNECTION_FACEBOOK:
				return (bool) ($this->facebook instanceof Facebook && $this->facebook->id);
			case IUserSocials::SOCIAL_CONNECTION_TWITTER:
				return (bool) ($this->twitter instanceof Twitter && $this->twitter->id);
			case IUserSocials::SOCIAL_CONNECTION_LINKEDIN:
				return (bool) ($this->linkedin instanceof Linkedin && $this->linkedin->id);
			default:
				return FALSE;
		}
	}

	public function getConnectionCount()
	{
		$allConnections = [
				IUserSocials::SOCIAL_CONNECTION_APP,
				IUserSocials::SOCIAL_CONNECTION_FACEBOOK,
				IUserSocials::SOCIAL_CONNECTION_GITHUB,
				IUserSocials::SOCIAL_CONNECTION_GOOGLE,
				IUserSocials::SOCIAL_CONNECTION_LINKEDIN,
				IUserSocials::SOCIAL_CONNECTION_TWITTER,
		];
		$count = 0;
		foreach ($allConnections as $connection) {
			if ($this->hasSocialConnection($connection)) {
				$count++;
			}
		}
		return $count;
	}

}