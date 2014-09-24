<?php

namespace App\Model\Storage;

use Nette\Http\Session,
	Nette\Http\SessionSection,
	App\Model\Entity\UserSettings,
	Kdyby\Doctrine\EntityManager;

/**
 * Storage for user's customization settings, saves data to session and db.
 */
class UserSettingsStorage extends \Nette\Object
{

	/** @var SessionSection */
	private $section;
	
	/** @var EntityManager */
	private $em;

	public function __set($name, $value)
	{
		$this->section->settings->$name = $value;
		return $this;
	}

	public function &__get($name)
	{
		return $this->section->setting->$name;
	}

	public function getSettings()
	{
		return $this->section->settings;
	}
	
	public function loadSettings($userId)
	{
		$settings = $this->em->createQueryBuilder()
				->select('us')
				->from('\App\Model\Entity\UserSettings', 'us')
				->join('App\Model\Entity\User', 'u')
				->where('u.id = ?1')
				->setParameter(1, $userId)
				->getQuery()->execute();
		
		if ($settings === NULL) {
			$this->setDefaults();
		}
		
		return $this;
	}

	public function setSettings(UserSettings $userSettings)
	{
		return $this->section->settings = $userSettings;
	}
	
	private function setDefaults()
	{
		$this->settings->setLanguage('en');
	}

	public function injectSession(Session $session)
	{
		$this->section = $session->getSection('userSettings');

		if (!isset($this->settings)) {
			$this->settings = new UserSettings();
			$this->setDefaults();
		}
	}

	public function injectEntityManager(EntityManager $em)
	{
		$this->em = $em;
	}

}
