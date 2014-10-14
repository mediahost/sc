<?php

namespace App\Model\Storage;

use Nette\Http\Session,
	Nette\Http\SessionSection,
	App\Model\Entity\UserSettings,
	Kdyby\Doctrine\EntityManager;

/**
 * Storage for user's customization settings, saves data to session and database.
 */
class UserSettingsStorage extends \Nette\Object
{

	/** @var SessionSection */
	private $section;

	/** @var EntityManager */
	private $em;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $userDao;

	/** @var array */
	private $defaults = [
		'language' => 'en',
	];

	/**
	 * @param int $userId
	 * @return UserSettingsStorage
	 */
	public function load($userId)
	{
		/* @var $settings \App\Model\Entity\UserSettings */
		$settings = $this->userDao->find($userId)->settings;

		// Preparing for serialization to session
		$this->em->detach($settings);
		$settings->user = NULL;
		$this->settings = $settings;

		return $this;
	}

	/**
	 * @return UserSettingsStorage
	 */
	public function save()
	{
		$qb = $this->em->createQueryBuilder();
		$qb->update('App\Model\Entity\UserSettings', 'us')
				->set('us.language', ':language')
				->where('us.id = :id')
				->setParameters([
					'language' => $this->settings->language,
					'id' => $this->settings->id,
				])
				->getQuery()
				->execute();

		return $this;
	}

	/**
	 * @return UserSettingsStorage
	 */
	public function wipe()
	{
		unset($this->section->settings);
		return $this;
	}

	/**
	 * @return UserSettings
	 */
	public function getSettings()
	{
		return $this->section->settings;
	}

	/**
	 * @param UserSettings $userSettings
	 * @return UserSettingsStorage
	 */
	public function setSettings(UserSettings $userSettings)
	{
		$this->section->settings = $userSettings;
		return $this;
	}

	/**
	 * @return UserSettingsStorage
	 */
	private function setDefaults()
	{
		$settings = new UserSettings();
		$settings->language = $this->defaults['language'];
		$this->settings = $settings;

		return $this;
	}

	/**
	 * @param string $language
	 * @return UserSettingsStorage
	 */
	public function setLanguage($language)
	{
		return $this->setProperty('language', $language);
	}

	/**
	 * @return sting
	 */
	public function getLanguage()
	{
		return $this->getProperty('language');
	}

	/**
	 * @param string $property
	 * @param mixed $value
	 * @return UserSettings
	 */
	private function setProperty($property, $value)
	{
		if ($value === $this->defaults[$property]) {
			$this->settings->$property = NULL;
		} else {
			$this->settings->$property = $value;
		}

		return $this;
	}

	/**
	 * @param string $property
	 * @return mixed
	 */
	private function getProperty($property)
	{
		if ($this->settings->$property === NULL) {
			return $this->defaults[$property];
		}

		return $this->settings->$property;
	}

	/**
	 * @param Session $session
	 */
	public function injectSession(Session $session)
	{
		$this->section = $session->getSection('userSettings');
		$this->section->warnOnUndefined = TRUE;

		if (!isset($this->section->settings)) {
			$this->setDefaults();
		}
	}

	/**
	 * @param EntityManager $em
	 */
	public function injectEntityManager(EntityManager $em)
	{
		$this->em = $em;
		$this->userDao = $this->em->getDao(\App\Model\Entity\User::getClassName());
	}

}
