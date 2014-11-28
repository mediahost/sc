<?php

namespace App\Model\Storage;

use App\Model\Entity;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;
use Nette\Security;
use Nette\Utils\ArrayHash;

/**
 * @author Martin Šifra <me@martinsifra.cz>
 * @author Petr Poupě <petr.poupe@gmail.com>
 * 
 * @property-read ArrayHash $pageInfo Getting page info like author and description
 * @property-read ArrayHash $pageControls Getting page controls like items per page
 * @property-read ArrayHash $expiration Expiration times
 * @property-read ArrayHash $languages Languages settings
 * @property-read ArrayHash $passwordsPolicy Password policy
 * @property-read Entity\PageConfigSettings $userPageSettings Get user page config entity
 * @property-read Entity\PageConfigSettings $pageSettings Get user page config settings like language (default with user setted values)
 * @property-read Entity\PageDesignSettings $userDesignSettings Get user design settings entity
 * @property-read Entity\PageDesignSettings $designSettings Get user design settings (default with user setted values)
 */
class SettingsStorage extends Object
{
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var ArrayHash */
	private $expiration;

	/** @var ArrayHash */
	private $languages;

	/** @var ArrayHash */
	private $passwordsPolicy;

	/** @var ArrayHash */
	private $pageControls;

	/** @var ArrayHash */
	private $pageInfo;

	/** @var ArrayHash */
	private $modules;

	/** @var ArrayHash */
	private $modulesSettings;

	/** @var Entity\PageConfigSettings */
	private $defaultPageSettings;

	/** @var Entity\PageDesignSettings */
	private $defaultDesignSettings;

	/** @var Entity\PageConfigSettings */
	private $userPageSettings;

	/** @var Entity\PageDesignSettings */
	private $userDesignSettings;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var GuestSettingsStorage @inject */
	public $guestStorage;

	// </editor-fold>

	public function save(Security\User $user)
	{
		if ($user->loggedIn && $user->id) {
			$userDao = $this->em->getDao(Entity\User::getClassName());
			$userEntity = $userDao->find($user->id);

			$this->userPageSettings->setUser($userEntity);
			$pageSettingsDao = $this->em->getDao(Entity\PageConfigSettings::getClassName());
			$pageSettingsDao->save($this->userPageSettings);

			$this->userDesignSettings->setUser($userEntity);
			$designSettingsDao = $this->em->getDao(Entity\PageDesignSettings::getClassName());
			$designSettingsDao->save($this->userDesignSettings);
		} else {
			if ($this->userPageSettings) {
				$this->guestStorage->setPageSettings($this->userPageSettings);
			}
			if ($this->userDesignSettings) {
				$this->guestStorage->setDesignSettings($this->userDesignSettings);
			}
		}
	}

	/**
	 * Set expiration times
	 * @param array $expiration
	 * @return self
	 */
	public function setExpiration(array $expiration)
	{
		$this->expiration = ArrayHash::from($expiration);
		return $this;
	}

	public function getExpiration()
	{
		return $this->expiration;
	}

	/**
	 * Set languages settings
	 * @param array $languages
	 * @return self
	 */
	public function setLanguages(array $languages)
	{
		$this->languages = ArrayHash::from($languages);
		return $this;
	}

	public function getLanguages()
	{
		return $this->languages;
	}

	/**
	 * Set passwords policy
	 * @param array $passwordsPolicy
	 * @return self
	 */
	public function setPasswordsPolicy(array $passwordsPolicy)
	{
		$this->passwordsPolicy = ArrayHash::from($passwordsPolicy);
		return $this;
	}

	public function getPasswordsPolicy()
	{
		return $this->passwordsPolicy;
	}

	/**
	 * Set page controls like items per page
	 * @param array $controls
	 * @return self
	 */
	public function setPageControls(array $controls)
	{
		$this->pageControls = ArrayHash::from($controls);
		return $this;
	}

	public function getPageControls()
	{
		return $this->pageControls;
	}

	/**
	 * Setting page info like author and description
	 * @param array $info
	 * @return self
	 */
	public function setPageInfo(array $info)
	{
		$this->pageInfo = ArrayHash::from($info);
		return $this;
	}

	public function getPageInfo()
	{
		return $this->pageInfo;
	}

	/**
	 * Set modules allowing and module settings
	 * @param array $modules
	 * @param array $settings
	 * @return self
	 */
	public function setModules(array $modules, array $settings)
	{
		$this->modules = ArrayHash::from($modules);
		$this->modulesSettings = ArrayHash::from($settings);
		return $this;
	}

	/**
	 * Check if name of module is allowed
	 * @param type $name
	 * @return boolean
	 */
	public function isAllowedModule($name)
	{
		if (isset($this->modules->$name) && $this->modules->$name === TRUE) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * If setting exists then return universal entity else return NULL
	 * @param type $name
	 * @return Entity\Special\UniversalDataEntity|NULL
	 */
	public function getModuleSettings($name)
	{
		if ($this->isAllowedModule($name) && isset($this->modulesSettings->$name)) {
			return new Entity\Special\UniversalDataEntity((array) $this->modulesSettings->$name);
		}
		return NULL;
	}

	/**
	 * Set default user preferences
	 * @param array $page
	 * @param array $design
	 * @return self
	 */
	public function setUserPreferences(array $page, array $design)
	{
		$this->defaultPageSettings = new Entity\PageConfigSettings;
		$this->defaultPageSettings->setValues($page);
		$this->defaultDesignSettings = new Entity\PageDesignSettings;
		$this->defaultDesignSettings->setValues($design);
		return $this;
	}

	/**
	 * Set user page config settings
	 * @param Entity\PageConfigSettings $settings
	 * @return self
	 */
	public function setUserPageSettings($settings)
	{
		if ($settings instanceof Entity\PageConfigSettings) {
			if ($settings->id) {
				$dao = $this->em->getDao(Entity\PageConfigSettings::getClassName());
				$settingsEntity = $dao->find($settings->id);
			} else {
				$settingsEntity = $settings;
			}
			if ($settingsEntity) {
				$this->userPageSettings = $settingsEntity;
			}
		}
		return $this;
	}

	public function &getUserPageSettings()
	{
		if (!$this->userPageSettings) {
			$this->userPageSettings = new Entity\PageConfigSettings;
		}
		return $this->userPageSettings;
	}

	public function getPageSettings($onlyDefault = FALSE)
	{
		$settings = $this->defaultPageSettings;
		if (!$onlyDefault && $this->userPageSettings) {
			$settings->setValues($this->userPageSettings->notNullValuesArray);
		}
		return $settings;
	}

	/**
	 * Set user page design settings
	 * @param Entity\PageDesignSettings $settings
	 * @return self
	 */
	public function setUserDesignSettings($settings)
	{
		if ($settings instanceof Entity\PageDesignSettings) {
			if ($settings->id) {
				$dao = $this->em->getDao(Entity\PageDesignSettings::getClassName());
				$settingsEntity = $dao->find($settings->id);
			} else {
				$settingsEntity = $settings;
			}
			if ($settingsEntity) {
				$this->userDesignSettings = $settingsEntity;
			}
		}
	}

	public function &getUserDesignSettings()
	{
		if (!$this->userDesignSettings) {
			$this->userDesignSettings = new Entity\PageDesignSettings;
		}
		return $this->defaultDesignSettings;
	}

	public function getDesignSettings($onlyDefault = FALSE)
	{
		$settings = $this->defaultDesignSettings;
		if (!$onlyDefault && $this->userDesignSettings) {
			$settings->setValues($this->userDesignSettings->notNullValuesArray);
		}
		return $settings;
	}

}
