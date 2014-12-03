<?php

namespace App\Extensions\Settings\Model\Service;

/**
 * PageInfoService
 * 
 * @author Petr Poupě <petr.poupe@gmail.com>
 * 
 * @property-read string $projectName Name of project
 * @property-read string $author
 * @property-read string $description
 */
class PageInfoService extends BaseService
{

	/** @return string */
	public function getProjectName()
	{
		if (isset($this->defaultStorage->pageInfo->projectName)) {
			return $this->defaultStorage->pageInfo->projectName;
		}
		return NULL;
	}

	/** @return string */
	public function getAuthor()
	{
		if (isset($this->defaultStorage->pageInfo->author)) {
			return $this->defaultStorage->pageInfo->author;
		}
		return NULL;
	}

	/** @return string */
	public function getDescription()
	{
		if (isset($this->defaultStorage->pageInfo->description)) {
			return $this->defaultStorage->pageInfo->description;
		}
		return NULL;
	}

}
