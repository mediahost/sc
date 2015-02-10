<?php

namespace App\Extensions\Settings\Model\Service;

/**
 * @property-read string $itemsPerPage
 * @property-read string $itemsPerRow
 * @property-read string $rowsPerPage
 */
class PageConfigService extends BaseService
{

	/** @return string */
	public function getItemsPerPage()
	{
		if (isset($this->defaultStorage->pageConfig->itemsPerPage)) {
			return $this->defaultStorage->pageConfig->itemsPerPage;
		}
		return NULL;
	}

	/** @return string */
	public function getItemsPerRow()
	{
		if (isset($this->defaultStorage->pageConfig->itemsPerRow)) {
			return $this->defaultStorage->pageConfig->itemsPerRow;
		}
		return NULL;
	}

	/** @return string */
	public function getRowsPerPage()
	{
		if (isset($this->defaultStorage->pageConfig->rowsPerPage)) {
			return $this->defaultStorage->pageConfig->rowsPerPage;
		}
		return NULL;
	}

}
