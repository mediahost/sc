<?php

namespace App\Extensions\Settings\Model\Service;

use App\Model\Entity\PageConfigSettings;
use Nette\Http\Request;

/**
 * @property-read string $language Default or user language
 * @property-read string $defaultLanguage
 * @property string $userLanguage
 * @property-read array $allowedLanguages
 * @property-read array $detectedLanguage
 */
class LanguageService extends BaseService
{

	/** @var Request @inject */
	public $httpRequest;

	/**
	 * @return string 
	 */
	public function getLanguage()
	{
		if ($this->user && $this->user->pageConfigSettings && $this->user->pageConfigSettings->language) {
			return $this->user->pageConfigSettings->language;
		}
		return $this->getDefaultLanguage();
	}

	/**
	 * @return string 
	 */
	public function getDefaultLanguage()
	{
		return $this->defaultStorage->languages->default;
	}

	/**
	 * @return string|NULL
	 */
	public function getUserLanguage()
	{
		if ($this->user && $this->user->pageConfigSettings && $this->user->pageConfigSettings->language) {
			return $this->user->pageConfigSettings->language;
		}
		return NULL;
	}

	/**
	 * Set and save user language
	 * @return self
	 */
	public function setUserLanguage($lang)
	{
		if ($this->user->id && $this->isAllowed($lang)) {
			if (!$this->user->pageConfigSettings instanceof PageConfigSettings) {
				$this->user->pageConfigSettings = new PageConfigSettings;
			}
			$this->user->pageConfigSettings->language = $lang;
			$this->saveUser();
		}
		return $this;
	}

	/**
	 * @return array 
	 */
	public function getAllowedLanguages()
	{
		return $this->defaultStorage->languages->allowed;
	}

	/**
	 * Check if language is allowed
	 * @param type $lang
	 * @return bool
	 */
	public function isAllowed($lang)
	{
		return array_key_exists($lang, $this->getAllowedLanguages());
	}

	/**
	 * Detect language from http request
	 * @return string return allowed lang code or default lang code
	 */
	public function getDetectedLanguage()
	{
		$detected = $this->httpRequest->detectLanguage(array_keys((array) $this->defaultStorage->languages->recognize));
		if ($detected && $this->isAllowed($detected)) {
				return $detected;
		}
		return $this->getDefaultLanguage();
	}

}
