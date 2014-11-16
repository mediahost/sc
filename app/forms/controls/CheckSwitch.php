<?php

namespace App\Forms\Controls;

use Nette;

/**
 * CheckSwitch
 *
 * @author Petr Poupě
 */
class CheckSwitch extends \Nette\Forms\Controls\Checkbox
{

	// <editor-fold defaultstate="collapsed" desc="constants & variables">
	// </editor-fold>

	public function __construct($label = NULL, $onText = NULL, $offText = NULL)
	{
		parent::__construct($label);
		$this->control->class = 'make-switch';
		if ($onText) {
			$this->setOnText($onText);
		}
		if ($offText) {
			$this->setOffText($offText);
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters">

	/**
	 *
	 * @param string $text
	 * @return self
	 */
	public function setOnText($text)
	{
		$attr = 'data-on-text';
		$this->control->$attr = $text;
		return $this;
	}

	/**
	 *
	 * @param string $text
	 * @return self
	 */
	public function setOffText($text)
	{
		$attr = 'data-off-text';
		$this->control->$attr = $text;
		return $this;
	}

	/**
	 *
	 * @param string $color
	 * @return self
	 */
	public function setOnColor($color)
	{
		$attr = 'data-on-color';
		$this->control->$attr = $color;
		return $this;
	}

	/**
	 *
	 * @param string $color
	 * @return self
	 */
	public function setOffColor($color)
	{
		$attr = 'data-off-color';
		$this->control->$attr = $color;
		return $this;
	}

	/**
	 *
	 * @param string $icon
	 * @return self
	 */
	public function setLabelIcon($icon)
	{
		$attr = 'data-label-icon';
		$this->control->$attr = $icon;
		return $this;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">

	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		return \Nette\Forms\Controls\BaseControl::getControl()->checked($this->value);
	}

	/**
	 * Generates label's HTML element.
	 * @param  string
	 * @return Nette\Utils\Html
	 */
	public function getLabel($caption = NULL)
	{
		return \Nette\Forms\Controls\BaseControl::getLabel($caption);
	}

	// </editor-fold>
}
