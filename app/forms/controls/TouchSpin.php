<?php

namespace App\Forms\Controls;

/**
 * TouchSpin
 *
 * @author Petr Poupě
 */
class TouchSpin extends \Nette\Forms\Controls\TextInput
{

    public function __construct($label = NULL)
    {
        parent::__construct($label);
        $this->control->class = "touchspin";
    }

    /**
     * 
     * @param type $value
     * @return self
     */
    public function setPrefix($value)
    {
        $attr = "data-prefix";
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
        $attr = "data-postfix";
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
        $attr = "data-buttondown-class";
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
        $attr = "data-buttonup-class";
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
        $attr = "data-min";
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
        $attr = "data-max";
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
        $attr = "data-step";
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
        $attr = "data-decimals";
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
        $attr = "data-boostat";
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
        $attr = "data-maxboostedstep";
        $this->control->$attr = $value;
        return $this;
    }

}
