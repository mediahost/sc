s<?php

namespace App;

/**
 * Helpers.
 *
 * @author Petr Poupě
 */
class Helpers
{

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new \LogicException("Cannot instantiate static class " . get_class($this));
	}

	// <editor-fold defaultstate="collapsed" desc="strings">

	/**
	 * Short for concatStrings()
	 * @author Petr Poupě
	 * @param string $first
	 * @param string $second
	 * @param string $separator
	 * @return string
	 */
	public static function concatTwoStrings($first = NULL, $second = NULL, $separator = " ")
	{
		return self::concatStrings($separator, $first, $second);
	}

	/**
	 * Accepts unlimited parameters or two parameters, where second is array
	 * @author Petr Poupě
	 * @param string $separator
	 * @return string|null
	 */
	public static function concatStrings($separator = " ")
	{
		$args = func_get_args();
		if (count($args) > 1) {
			$separator = is_string($args[0]) ? $args[0] : $separator;
			array_shift($args);
			if (count($args) == 1 && is_array($args[0])) {
				$args = $args[0];
			}
			$string = NULL;
			foreach ($args as $item) {
				if ($string === NULL) {
					$string = $item;
				} else if ($item !== NULL) {
					$string .= $separator . $item;
				}
			}
			return $string;
		} else {
			return NULL;
		}
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="date">

	/**
	 * Matches each symbol of PHP date format standard
	 * with jQuery equivalent codeword
	 * @author Tristan Jahier
	 * @return string
	 */
	public static function dateformatPHP2JS($phpDate)
	{
		$symbols = array(
			// Day
			'd' => 'dd',
			'D' => 'D',
			'j' => 'd',
			'l' => 'DD',
			'N' => '',
			'S' => '',
			'w' => '',
			'z' => 'o',
			// Week
			'W' => '',
			// Month
			'F' => 'MM',
			'm' => 'mm',
			'M' => 'M',
			'n' => 'm',
			't' => '',
			// Year
			'L' => '',
			'o' => '',
			'Y' => 'yyyy',
			'y' => 'y',
			// Time
			'a' => '',
			'A' => '',
			'B' => '',
			'g' => '',
			'G' => '',
			'h' => '',
			'H' => '',
			'i' => '',
			's' => '',
			'u' => ''
		);
		$jsDate = "";
		$escaping = false;
		for ($i = 0; $i < strlen($phpDate); $i++) {
			$char = $phpDate[$i];
			if ($char === '\\') { // PHP date format escaping character
				$i++;
				if ($escaping) {
					$jsDate .= $phpDate[$i];
				} else {
					$jsDate .= '\'' . $phpDate[$i];
				}
				$escaping = true;
			} else {
				if ($escaping) {
					$jsDate .= "'";
					$escaping = false;
				}
				if (isset($symbols[$char])) {
					$jsDate .= $symbols[$char];
				} else {
					$jsDate .= $char;
				}
			}
		}
		return $jsDate;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="transformation">

	/**
	 * Function to translate link in text to HTML format of link
	 * @param type $text
	 * @param type $class
	 * @param type $target
	 * @return type
	 */
	public static function linkToAnchor($text, $class = NULL, $target = "_blank")
	{
		return preg_replace('@((http|https)://([\w-.]+)+(:\d+)?(/([\w/_\-.]*(\?\S+)?)?)?)@'
				, '<a href="$1"' . ($class ? (' class="' . $class . '"') : '') . ($target ? ' target="' . $target . '"' : '') . '>$1</a>'
				, $text);
	}

	// </editor-fold>
}
