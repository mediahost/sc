<?php

namespace App\Extensions\Settings\Model\Service;

use App\Model\Entity\PageDesignSettings;

/**
 * DesignService
 * 
 * @author Petr Poupě <petr.poupe@gmail.com>
 * 
 * @property-read PageDesignSettings $settings Default settings extended by user settings
 * @property-read PageDesignSettings $userSettings User settings
 * @property-read array $colors Allowed colors
 * @property-write string $color
 * @property-write bool $layoutBoxed
 * @property-write bool $containerBgSolid
 * @property-write bool $headerFixed
 * @property-write bool $footerFixed
 * @property-write bool $sidebarClosed
 * @property-write bool $sidebarFixed
 * @property-write bool $sidebarReversed
 * @property-write bool $sidebarMenuHover
 * @property-write bool $sidebarMenuLight
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

	/** @return PageDesignSettings */
	public function getUserSettings()
	{
		if (!$this->user->pageDesignSettings) {
			$this->user->pageDesignSettings = new PageDesignSettings;
		}
		return $this->user->pageDesignSettings;
	}

	/** @return bool */
	public function isAllowedColor($color)
	{
		return array_key_exists($color, $this->colors);
	}

	/** @return self */
	public function setColor($color)
	{
		if ($this->isAllowedColor($color)) {
			$pageDesignSettings = $this->getUserSettings();
			if ($color === 'default') {
				$color = NULL;
			}
			$pageDesignSettings->color = $color;
			$this->saveUser();
		}
		return $this;
	}

	/** @return self */
	public function setSidebarClosed($value = TRUE)
	{
		$pageDesignSettings = $this->getUserSettings();
		$pageDesignSettings->sidebarClosed = $value;
		$this->saveUser();
		return $this;
	}

}
