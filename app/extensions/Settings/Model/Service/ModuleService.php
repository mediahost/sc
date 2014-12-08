<?php

namespace App\Extensions\Settings\Model\Service;

use App\Model\Entity\Special\UniversalDataEntity;

/**
 * ModuleService
 * 
 * @author Petr Poupě <petr.poupe@gmail.com>
 * 
 * @property-read string $length Length of password
 */
class ModuleService extends BaseService
{

	/**
	 * Check if name of module is allowed
	 * @param type $name
	 * @return boolean
	 */
	public function isAllowedModule($name)
	{
		if (isset($this->defaultStorage->modules->$name) && $this->defaultStorage->modules->$name === TRUE) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * If setting exists then return universal entity else return NULL
	 * @param type $name
	 * @return UniversalDataEntity|NULL
	 */
	public function getModuleSettings($name)
	{
		if ($this->isAllowedModule($name) && isset($this->defaultStorage->moduleSettings->$name)) {
			return new UniversalDataEntity((array) $this->defaultStorage->moduleSettings->$name);
		}
		return NULL;
	}

}
