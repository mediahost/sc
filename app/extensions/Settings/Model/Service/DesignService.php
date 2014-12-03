<?php

namespace App\Extensions\Settings\Model\Service;

use App\Model\Entity\PageDesignSettings;

/**
 * DesignService
 * 
 * @author Petr Poupě <petr.poupe@gmail.com>
 * 
 * @property PageDesignSettings $settings
 */
class DesignService extends BaseService
{

	/** @return PageDesignSettings */
	public function getSettings()
	{
		$settings = new PageDesignSettings();
		$settings->setValues((array) $this->defaultStorage->design);
		if ($this->user) {
			$settings->append($this->user->pageDesignSettings);
		}
		return $settings;
	}

}
