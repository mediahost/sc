<?php

namespace App\Extensions\Settings\Model\Storage;

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

	/** @var EntityManager @inject */
	public $em;

	/** @var SessionSection */
	private $section;

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
			$this->section->page = new PageConfigSettings;
		}
		if (!isset($this->section->design)) {
			$this->section->design = new PageDesignSettings;
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

	/** @return PageConfigSettings */
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

	/** @return PageDesignSettings */
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

}
