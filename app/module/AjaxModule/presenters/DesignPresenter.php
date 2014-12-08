<?php

namespace App\AjaxModule\Presenters;

/**
 * Ajax design
 */
class DesignPresenter extends BasePresenter
{

	public function actionSetColor($color)
	{
		if ($this->designService->isAllowedColor($color)) {
			$this->designService->color = $color;
			$this->addData('color', $color);
		} else {
			$this->setError('This color isn\'t supported.');
		}
	}

	public function actionSetLayout($layoutOption, $sidebarOption, $headerOption
	, $footerOption, $sidebarPosOption, $sidebarStyleOption, $sidebarMenuOption)
	{
		$userSettings = $this->designService->userSettings;
		$userSettings->layoutBoxed = $layoutOption === 'boxed';
		$userSettings->sidebarFixed = $sidebarOption === 'fixed';
		$userSettings->headerFixed = $headerOption === 'fixed';
		$userSettings->footerFixed = $footerOption === 'fixed';
		$userSettings->sidebarReversed = $sidebarPosOption === 'right';
		$userSettings->sidebarMenuLight = $sidebarStyleOption === 'light';
		$userSettings->sidebarMenuHover = $sidebarMenuOption === 'hover';
		$this->designService->saveUser();

		$this->addData('layoutBoxed', $this->designService->settings->layoutBoxed);
		$this->addData('sidebarFixed', $this->designService->settings->sidebarFixed);
		$this->addData('headerFixed', $this->designService->settings->headerFixed);
		$this->addData('footerFixed', $this->designService->settings->footerFixed);
		$this->addData('sidebarReversed', $this->designService->settings->sidebarReversed);
		$this->addData('sidebarMenuLight', $this->designService->settings->sidebarMenuLight);
		$this->addData('sidebarMenuHover', $this->designService->settings->sidebarMenuHover);
	}

	public function actionSetSidebarClosed($value)
	{
		$this->designService->sidebarClosed = $value;
		$this->addData('sidebarClosed', $this->designService->settings->sidebarClosed);
	}

}
