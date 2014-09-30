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
		$user->settings = $settings;
		$this->em->persist($settings);
		$this->em->persist($user);
		$this->em->flush();
		
		$this->loadSettings($userId);
	}
	
	public function loadSettings($userId)
	{
		/* @var $settings \App\Model\Entity\UserSettings */
		$settings = $this->em->createQueryBuilder()
				->select('us')
				->from('\App\Model\Entity\UserSettings', 'us')
				->join('App\Model\Entity\User', 'u')
				->where('u.id = ?1')
				->setParameter(1, $userId)
				->getQuery()
				->getSingleResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
		
		\Tracy\Debugger::barDump($settings);
		
		if ($settings === NULL) {
			$this->setDefaults();
		} else {
			$entity = new UserSettings();
			$entity->language = $settings['language'];
			$this->settings = $entity;
		}
		
		return $this;
	}
	
	public function wipe()
	{
//		$this->section->remove();
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
//		$this->setDefaults();
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
