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
	
	/** @var \Kdyby\Doctrine\EntityDao */
	private $userDao;

//	public function __set($name, $value)
//	{
//		$this->section->settings->$name = $value;
//		return $this;
//	}
//
//	public function __get($name)
//	{
//		return $this->section->settings->$name;
//	}
	
	public function getSettings()
	{
		return $this->section->settings;
	}
	
	public function save($userId, $settings)
	{
		$user = $this->userDao->find($userId);
		$settings->user = $user;
		$this->em->merge($settings);
		$this->em->flush();
		
		$this->loadSettings($userId);
	}
	
	public function loadSettings($userId)
	{
		/* @var $settings \App\Model\Entity\UserSettings */
		$settings = $this->userDao->find($userId)->settings;
		
		if ($settings === NULL) {
			$this->setDefaults();
		} else {
			$this->em->detach($settings);
			$settings->user = NULL;
			$this->settings = $settings;
		}
		
		return $this;
	}
	
	public function wipe()
	{
		unset($this->section->settings);
	}

	public function setSettings(UserSettings $userSettings)
	{
		return $this->section->settings = $userSettings;
	}
	
	private function setDefaults()
	{
		$this->section->settings = $this->getDefaults();
	}
	
	public function getDefaults()
	{
		$settings = new UserSettings();
		$settings->setLanguage('en');
		return $settings;
	}

	public function setLanguage($language)
	{
		$this->settings->language = $language;
		return $this;
	}
	
	public function getLanguage()
	{
		return $this->settings->language;
	}
	
	public function injectSession(Session $session)
	{
		$this->section = $session->getSection('userSettings');
		$this->section->warnOnUndefined = TRUE;

		if (!isset($this->section->settings)) {
			$this->setDefaults();
		}
	}

	public function injectEntityManager(EntityManager $em)
	{
		$this->em = $em;
		$this->userDao = $this->em->getDao(\App\Model\Entity\User::getClassName());
	}

}
