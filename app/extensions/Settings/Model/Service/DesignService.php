<?php

namespace App\Extensions\Settings\Model\Service;

use App\Model\Entity\PageDesignSettings;

/**
 * DesignService
 * 
 * @author Petr Poupě <petr.poupe@gmail.com>
 * 
 * @property PageDesignSettings $settings
 * @property array $colors Allowed colors
 * @property-write string $color
 */
class DesignService extends BaseService
{

	/** @return PageDesignSettings */
	public function getSettings()
	{
		$defaultSettings = new PageDesignSettings();
		$defaultSettings->setValues((array) $this->defaultStorage->design);
		if ($this->user && $this->user->pageDesignSettings) {
			$settings = $this->user->pageDesignSettings;
			$settings->append($defaultSettings);
		} else {
			$settings = $defaultSettings;
		}
		return $settings;
	}

	/** @return array */
	public function getColors()
	{
		return $this->defaultStorage->design->colors;
	}

	public function setColor($color)
	{
		if ($this->isAllowedColor($color)) {
			if (!$this->user->pageDesignSettings) {
				$this->user->pageDesignSettings = new PageDesignSettings;
			}
			if ($color === 'default') {
				$color = NULL;
			}
			$this->user->pageDesignSettings->color = $color;
			$this->saveUser();
		}
	}

	public function isAllowedColor($color)
	{
		return array_key_exists($color, $this->colors);
	}

}
