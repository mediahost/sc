<?php

namespace App\Forms\Controls;

/**
 * TouchSpin
 *
 * @author Petr Poupě
 */
class TouchSpin extends \Nette\Forms\Controls\TextInput
{

	// <editor-fold defaultstate="collapsed" desc="constants & variables">

	const SIZE_FLUID = NULL;
	const SIZE_XL = 'input-xlarge';
	const SIZE_L = 'input-large';
	const SIZE_M = 'input-medium';
	const SIZE_S = 'input-small';
	const SIZE_XS = 'input-xsmall';

	// </editor-fold>

	public function __construct($label = NULL)
	{
		parent::__construct($label);
		$this->control->class = 'touchspin';
	}

	// <editor-fold defaultstate="collapsed" desc="setters">

	/**
	 *
	 * @param type $size
	 * @return self
	 */
	public function setSize($size = self::SIZE_FLUID)
	{
		switch ($size) {
			case self::SIZE_FLUID:
			case self::SIZE_XL:
			case self::SIZE_L:
			case self::SIZE_M:
			case self::SIZE_S:
			case self::SIZE_XS:
				break;
			default:
				$size = self::SIZE_FLUID;
				break;
		}
		$attr = 'data-size';
		$this->control->$attr = $size;
		return $this;
	}

	/**
	 *
	 * @param type $value
	 * @return self
	 */
	public function setPrefix($value)
	{
		$attr = 'data-prefix';
		$this->control->$attr = $value;
		return $this;
	}

	/**
	 *
	 * @param type $value
	 * @return self
	 */
	public function setPostfix($value)
	{
		$attr = 'data-postfix';
		$this->control->$attr = $value;
		return $this;
	}

	/**
	 *
	 * @param type $value
	 * @return self
	 */
	public function setButtonDownClass($value)
	{
		$attr = 'data-buttondown-class';
		$this->control->$attr = $value;
		return $this;
	}

	/**
	 *
	 * @param type $value
	 * @return self
	 */
	public function setButtonUpClass($value)
	{
		$attr = 'data-buttonup-class';
		$this->control->$attr = $value;
		return $this;
	}

	/**
	 *
	 * @param type $value
	 * @return self
	 */
	public function setMin($value)
	{
		$attr = 'data-min';
		$this->control->$attr = $value;
		return $this;
	}

	/**
	 *
	 * @param type $value
	 * @return self
	 */
	public function setMax($value)
	{
		$attr = 'data-max';
		$this->control->$attr = $value;
		return $this;
	}

	/**
	 *
	 * @param type $value
	 * @return self
	 */
	public function setStep($value)
	{
		$attr = 'data-step';
		$this->control->$attr = $value;
		return $this;
	}

	/**
	 *
	 * @param type $value
	 * @return self
	 */
	public function setDecimals($value)
	{
		$attr = 'data-decimals';
		$this->control->$attr = $value;
		return $this;
	}

	/**
	 *
	 * @param type $value
	 * @return self
	 */
	public function setBoostat($value)
	{
		$attr = 'data-boostat';
		$this->control->$attr = $value;
		return $this;
	}

	/**
	 *
	 * @param type $value
	 * @return self
	 */
	public function setMaxBoostedStep($value)
	{
		$attr = 'data-maxboostedstep';
		$this->control->$attr = $value;
		return $this;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">
	// </editor-fold>
}
