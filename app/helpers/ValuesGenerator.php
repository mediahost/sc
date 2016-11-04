<?php

namespace App;

use Nette\Utils\DateTime;

class ValuesGenerator
{

	public static function isFilled($fillProbability = 50)
	{
		return rand(0, 99) < $fillProbability;
	}

	public static function selectIndexFromList($list, $fillProbability = 50)
	{
		if (!self::isFilled($fillProbability)) {
			return null;
		}
		$keys = array_keys($list);
		return $keys[rand(0, count($keys) - 1)];
	}

	public static function selectValueFromList($list, $fillProbability = 50)
	{
		if (!self::isFilled($fillProbability)) {
			return null;
		}
		$keys = array_keys($list);
		$key = $keys[rand(0, count($keys) - 1)];
		return $list[$key];
	}

	public static function selectMultiIndexFromList($list, $valuesCount = null)
	{
		$result = [];
		$keys = array_keys($list);
		$valuesCount = $valuesCount ? $valuesCount : rand(0, count($keys) - 1);
		while ($valuesCount--) {
			$key = $keys[rand(0, count($keys) - 1)];
			if (!in_array($key, $result)) {
				$result[] = $key;
			}
		}
		return $result;
	}

	public static function selectMultiValuesFromList($list, $valuesCount = null)
	{
		$result = [];
		$keys = array_keys($list);
		$valuesCount = $valuesCount ? $valuesCount : rand(0, count($keys) - 1);
		while ($valuesCount--) {
			$key = $keys[rand(0, count($keys) - 1)];
			if (!in_array($list[$key], $list)) {
				$result[] = $list[$key];
			}
		}
		return $result;
	}

	public static function generatePastDate()
	{
		$daysToPast = rand(20 * 365, 60 * 365);
		$date = new DateTime();
		return $date->modify("-{$daysToPast}days");
	}

	public static function generateFeatureDate()
	{
		$daysInFeature = rand(10, 100);
		$date = new DateTime();
		return $date->modify("+{$daysInFeature}days");
	}

	public static function generateNumberString($length, $fillProbability = 50)
	{
		if (!self::isFilled($fillProbability)) {
			return null;
		}
		$digits = '1234567890';
		$result = '';
		while ($length--) {
			$result .= $digits[rand(0, strlen($digits) - 1)];
		}
		return $result;
	}

	public static function generateName($fillProbability = 50)
	{
		if (!self::isFilled($fillProbability)) {
			return '';
		}
		$result = '';
		$vowels = 'aeiouy';
		$consonants = 'bcdfghjklmnprstvwz';

		$syllableCount = rand(2, 4);
		while ($syllableCount--) {
			$syllable = $consonants[rand(0, strlen($consonants) - 1)]
				. $vowels[rand(0, strlen($vowels) - 1)];
			$result .= $syllable;
		}
		return $result;
	}

	public static function generateText($fillProbability = 50)
	{
		if (!self::isFilled($fillProbability)) {
			return '';
		}
		$result = '';
		$wordsCount = rand(1, 50);
		while ($wordsCount--) {
			$result .= self::generateName(100);
		}
		return $result;
	}

}