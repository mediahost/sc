<?php

namespace App\Model\Storage;

use App\Model\Entity\PageConfigSettings;
use App\Model\Entity\PageDesignSettings;
use App\Model\Entity\User;
use Kdyby\Doctrine\EntityManager;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Object;

/**
 * Storage for unsigned user's customization settings
 */
class GuestSettingsStorage extends Object
{

	/** @var SessionSection */
	private $section;

	/** @var EntityManager @inject */
	public $em;

	/**
	 * @param Session $session
	 */
	public function injectSession(Session $session)
	{
		$this->section = $session->getSection('guestSettings');
		$this->section->warnOnUndefined = TRUE;
		$this->init();
	}

	private function init()
	{
		if (!isset($this->section->page)) {
			$this->section->page = NULL;
		}
		if (!isset($this->section->design)) {
			$this->section->design = NULL;
		}
	}

	/**
	 * @param PageConfigSettings $settings
	 * @return self
	 */
	public function setPageSettings(PageConfigSettings $settings)
	{
		$this->section->page = $settings;
		return $this;
	}

	/** @return self */
	public function getPageSettings()
	{
		return $this->section->page;
	}

	/**
	 * @param PageDesignSettings $settings
	 * @return self
	 */
	public function setDesignSettings(PageDesignSettings $settings)
	{
		$this->section->design = $settings;
		return $this;
	}

	/** @return self */
	public function getDesignSettings()
	{
		return $this->section->design;
	}

	/** @return self */
	public function wipe()
	{
		unset($this->section->page);
		unset($this->section->design);
		return $this;
	}

	public function save($userId)
	{
		$userDao = $this->em->getDao(User::getClassName());
		$userEntity = $userDao->find($userId);
		if ($userEntity) {
			if ($this->section->page && $userEntity->pageConfigSettings instanceof PageConfigSettings) {
				$pageSettings = $userEntity->pageConfigSettings->append($this->section->page);
				$pageSettingsDao = $this->em->getDao(PageConfigSettings::getClassName());
				$pageSettingsDao->save($pageSettings);
			}
			if ($this->section->design && $userEntity->pageDesignSettings instanceof PageDesignSettings) {
				$designSettings = $userEntity->pageDesignSettings->append($this->section->design);
				$designSettingsDao = $this->em->getDao(PageDesignSettings::getClassName());
				$designSettingsDao->save($designSettings);
			}
		}
		return $this;
	}

}
