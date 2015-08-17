<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property string $language
 * @property string $languageName
 * @property string $listening
 * @property string $listeningName
 * @property string $reading
 * @property string $readingName
 * @property string $spokenInteraction
 * @property string $spokenInteractionName
 * @property string $spokenProduction
 * @property string $spokenProductionName
 * @property string $writing
 * @property string $writingName
 * @property Cv $cv
 */
class Language extends BaseEntity
{

	use Identifier;

	/** @ORM\ManyToOne(targetEntity="Cv", inversedBy="works") */
	protected $cv;

	/** @ORM\Column(type="string", length=5, nullable=false) */
	protected $language;

	/** @ORM\Column(type="string", length=5, nullable=true) */
	protected $listening;

	/** @ORM\Column(type="string", length=5, nullable=true) */
	protected $reading;

	/** @ORM\Column(type="string", length=5, nullable=true) */
	protected $spokenInteraction;

	/** @ORM\Column(type="string", length=5, nullable=true) */
	protected $spokenProduction;

	/** @ORM\Column(type="string", length=5, nullable=true) */
	protected $writing;

	public function __construct()
	{
		parent::__construct();
	}

	public function __toString()
	{
		return self::getLanguageNameById($this->language);
	}
	
	public function getLanguageName()
	{
		return self::getLanguageNameById($this->language);
	}
	
	public function getListeningName()
	{
		return self::getLanguageLevelName($this->listening);
	}
	
	public function getReadingName()
	{
		return self::getLanguageLevelName($this->reading);
	}
	
	public function getSpokenInteractionName()
	{
		return self::getLanguageLevelName($this->spokenInteraction);
	}
	
	public function getSpokenProductionName()
	{
		return self::getLanguageLevelName($this->spokenProduction);
	}
	
	public function getWritingName()
	{
		return self::getLanguageLevelName($this->writing);
	}

	public static function getLanguagesList()
	{
		return [
            'aa' => 'Afar',
            'ab' => 'Abkhazian',
            'ae' => 'Avestan',
            'af' => 'Afrikaans',
            'ak' => 'Akan',
            'am' => 'Amharic',
            'an' => 'Aragonese',
            'ar' => 'Arabic',
            'as' => 'Assamese',
            'av' => 'Avaric',
            'ay' => 'Aymara',
            'az' => 'Azerbaijani',
            'ba' => 'Bashkir',
            'be' => 'Belarusian',
            'bg' => 'Bulgarian',
            'bh' => 'Bihari',
            'bi' => 'Bislama',
            'bm' => 'Bambara',
            'bn' => 'Bengali',
            'bo' => 'Tibetan',
            'br' => 'Breton',
            'bs' => 'Bosnian',
            'ca' => 'Catalan',
            'ce' => 'Chechen',
            'ch' => 'Chamorro',
            'co' => 'Corsican',
            'cr' => 'Cree',
            'cs' => 'Czech',
            'cu' => 'Church Slavic',
            'cv' => 'Chuvash',
            'cy' => 'Welsh',
            'da' => 'Danish',
            'de' => 'German',
            'dv' => 'Divehi',
            'dz' => 'Dzongkha',
            'ee' => 'Ewe',
            'el' => 'Greek',
            'en' => 'English',
            'eo' => 'Esperanto',
            'es' => 'Spanish',
            'et' => 'Estonian',
            'eu' => 'Basque',
            'fa' => 'Persian',
            'ff' => 'Fulah',
            'fi' => 'Finnish',
            'fj' => 'Fijian',
            'fo' => 'Faroese',
            'fr' => 'French',
            'fy' => 'Western Frisian',
            'ga' => 'Irish',
            'gd' => 'Scottish Gaelic',
            'gl' => 'Galician',
            'gn' => 'Guarani',
            'gu' => 'Gujarati',
            'gv' => 'Manx',
            'ha' => 'Hausa',
            'he' => 'Hebrew',
            'hi' => 'Hindi',
            'ho' => 'Hiri Motu',
            'hr' => 'Croatian',
            'ht' => 'Haitian',
            'hu' => 'Hungarian',
            'hy' => 'Armenian',
            'hz' => 'Herero',
            'ia' => 'Interlingua',
            'id' => 'Indonesian',
            'ie' => 'Interlingue',
            'ig' => 'Igbo',
            'ii' => 'Sichuan Yi',
            'ik' => 'Inupiaq',
            'io' => 'Ido',
            'is' => 'Icelandic',
            'it' => 'Italian',
            'iu' => 'Inuktitut',
            'ja' => 'Japanese',
            'jv' => 'Javanese',
            'ka' => 'Georgian',
            'kg' => 'Kongo',
            'ki' => 'Kikuyu',
            'kj' => 'Kwanyama',
            'kk' => 'Kazakh',
            'kl' => 'Kalaallisut',
            'km' => 'Khmer',
            'kn' => 'Kannada',
            'ko' => 'Korean',
            'kr' => 'Kanuri',
            'ks' => 'Kashmiri',
            'ku' => 'Kurdish',
            'kv' => 'Komi',
            'kw' => 'Cornish',
            'ky' => 'Kirghiz',
            'la' => 'Latin',
            'lb' => 'Luxembourgish',
            'lg' => 'Ganda',
            'li' => 'Limburgish',
            'ln' => 'Lingala',
            'lo' => 'Lao',
            'lt' => 'Lithuanian',
            'lu' => 'Luba-Katanga',
            'lv' => 'Latvian',
            'mg' => 'Malagasy',
            'mh' => 'Marshallese',
            'mi' => 'Maori',
            'mk' => 'Macedonian',
            'ml' => 'Malayalam',
            'mn' => 'Mongolian',
            'mr' => 'Marathi',
            'ms' => 'Malay',
            'mt' => 'Maltese',
            'my' => 'Burmese',
            'na' => 'Nauru',
            'nb' => 'Norwegian Bokmal',
            'nd' => 'North Ndebele',
            'ne' => 'Nepali',
            'ng' => 'Ndonga',
            'nl' => 'Dutch',
            'nn' => 'Norwegian Nynorsk',
            'no' => 'Norwegian',
            'nr' => 'South Ndebele',
            'nv' => 'Navajo',
            'ny' => 'Chichewa',
            'oc' => 'Occitan',
            'oj' => 'Ojibwa',
            'om' => 'Oromo',
            'or' => 'Oriya',
            'os' => 'Ossetian',
            'pa' => 'Panjabi',
            'pi' => 'Pali',
            'pl' => 'Polish',
            'ps' => 'Pashto',
            'pt' => 'Portuguese',
            'qu' => 'Quechua',
            'rm' => 'Raeto-Romance',
            'rn' => 'Kirundi',
            'ro' => 'Romanian',
            'ru' => 'Russian',
            'rw' => 'Kinyarwanda',
            'sa' => 'Sanskrit',
            'sc' => 'Sardinian',
            'sd' => 'Sindhi',
            'se' => 'Northern Sami',
            'sg' => 'Sango',
            'si' => 'Sinhala',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'sm' => 'Samoan',
            'sn' => 'Shona',
            'so' => 'Somali',
            'sq' => 'Albanian',
            'sr' => 'Serbian',
            'ss' => 'Swati',
            'st' => 'Southern Sotho',
            'su' => 'Sundanese',
            'sv' => 'Swedish',
            'sw' => 'Swahili',
            'ta' => 'Tamil',
            'te' => 'Telugu',
            'tg' => 'Tajik',
            'th' => 'Thai',
            'ti' => 'Tigrinya',
            'tk' => 'Turkmen',
            'tl' => 'Tagalog',
            'tn' => 'Tswana',
            'to' => 'Tonga',
            'tr' => 'Turkish',
            'ts' => 'Tsonga',
            'tt' => 'Tatar',
            'tw' => 'Twi',
            'ty' => 'Tahitian',
            'ug' => 'Uighur',
            'uk' => 'Ukrainian',
            'ur' => 'Urdu',
            'uz' => 'Uzbek',
            've' => 'Venda',
            'vi' => 'Vietnamese',
            'vo' => 'Volapuk',
            'wa' => 'Walloon',
            'wo' => 'Wolof',
            'xh' => 'Xhosa',
            'yi' => 'Yiddish',
            'yo' => 'Yoruba',
            'za' => 'Zhuang',
            'zh' => 'Chinese',
            'zu' => 'Zulu',
		];
	}

	public static function getLanguageLevelList()
	{
		return [
			'1' => 'Basic User (A1)',
			'2' => 'Basic User (A2)',
			'3' => 'Independent user (B1)',
			'4' => 'Independent user (B2)',
			'5' => 'Proficient user (C1)',
			'6' => 'Proficient user (C2)',
		];
	}

	public static function getLanguageNameById($id)
	{
		$languages = self::getLanguagesList();
		if (array_key_exists($id, $languages)) {
			return $languages[$id];
		}
		return NULL;
	}

	public static function getLanguageLevelName($id)
	{
		$levels = self::getLanguageLevelList();
		if (array_key_exists($id, $levels)) {
			return $levels[$id];
		}
		return NULL;
	}

}
