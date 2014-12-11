<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\MappedSuperclass()
 * @method void setCurrentLocale(mixed $locale) the current locale
 */
abstract class BaseTranslatable extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	public function __construct($currentLocale = NULL)
	{
		parent::__construct();
		if ($currentLocale) {
			$this->setCurrentLocale($currentLocale);
		}
	}

	public function __call($method, $arguments)
	{
		return $this->proxyCurrentLocaleTranslation($method, $arguments);
	}

	/**
	 * Translation properties can get only by getProperty()
	 * For all tranlation property redirect $this->property to $this->getProperty()
	 * @param type $name
	 * @return type
	 */
	public function &__get($name)
	{
		$isTranslationProperty = property_exists(static::getTranslationEntityClass(), $name);
		$isBehaviorProperty = self::isBehaviorProperty($name);
		if (!$isBehaviorProperty && $isTranslationProperty && func_num_args() === 1) {
			$method = 'get' . ucfirst($name);
			$val = static::__call($method, []);
			return $val;
		}
		return parent::__get($name);
	}

	/**
	 * Translation properties can set only by setProperty()
	 * For all tranlation property redirect $this->property = $value to $this->setProperty($value)
	 * @param type $name
	 * @return type
	 */
	public function __set($name, $value)
	{
		$isTranslationProperty = property_exists(static::getTranslationEntityClass(), $name);
		$isBehaviorProperty = self::isBehaviorProperty($name);
		if (!$isBehaviorProperty && $isTranslationProperty) {
			$method = 'set' . ucfirst($name);
			return $this->$method($value);
		}
		parent::__set($name, $value);
	}

	static private function isBehaviorProperty($propertyName)
	{
		return property_exists('Knp\DoctrineBehaviors\Model\Translatable\TranslationProperties', $propertyName);
	}

}
