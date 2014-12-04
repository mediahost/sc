<?php

namespace App\Extensions\Settings\Model\Service;

use Nette\Http\Request;

/**
 * LanguageService
 * 
 * @author Petr Poupě <petr.poupe@gmail.com>
 * 
 * @property-read string $language Default or user language
 * @property-read string $defaultLanguage
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
