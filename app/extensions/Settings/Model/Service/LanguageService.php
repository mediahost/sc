<?php

namespace App\Extensions\Settings\Model\Service;

/**
 * LanguageService
 * 
 * @author Petr Poupě <petr.poupe@gmail.com>
 * 
 * @property-read string $language Default or user language
 * @property-read string $defaultLanguage
 * @property-read array $allowedLanguages
 */
class LanguageService extends BaseService
{

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
	 * Recognize code and return recognized language or default language
	 * @param string $code
	 * @return string 
	 */
	public function recognizeLanguage($code)
	{
		$languages = $this->defaultStorage->languages->recognize;
		if (array_key_exists($code, $languages)) {
			return $languages[$code];
		}
		return $this->getDefaultLanguage();
	}

}
