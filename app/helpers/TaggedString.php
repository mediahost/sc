<?php

namespace App;

use GettextTranslator\Gettext;

/**
 * String with tags to replace
 *
 * @author Petr Poupě
 */
class TaggedString
{

	/** @var string */
	private $taggedString;

	/** @var array */
	private $replacements = [];

	/** @var Gettext */
	private $translator;

	public function __construct($taggedString, array $replacements)
	{
		$this->setTaggedString($taggedString);
		$this->setReplacements($replacements);
	}
	
	public function setTaggedString($string)
	{
		$this->taggedString = $string;
		return $this;
	}
	
	public function setReplacements(array $replacements)
	{
		$this->replacements = $replacements;
		return $this;
	}
	
	public function setTranslator(Gettext $translator)
	{
		$this->translator = $translator;
		return $this;
	}

	public function __toString()
	{
		$string = $this->translator ? $this->translator->translate($this->taggedString) : $this->taggedString;
		return Helpers::replaceMyTag($string, $this->replacements);
	}

}
