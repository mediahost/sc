<?php

namespace App\Components\Job;

/**
 * Description of OffersControl
 *
 */
class OffersControl extends \App\Components\BaseControl
{
	/** @var array */
	public $onAfterSave = [];
	
	
	protected function createComponentForm()
	{
		
	}
	
	public function formSucceeded(Form $form, $values)
	{
		
	}
	
	protected function load(ArrayHash $values)
	{
		
	}
	
	private function save()
	{
		
	}
	
	/** @return array */
	protected function getDefaults()
	{
		
	}
}

interface IOffersControlFactory
{

	/** @return OffersControl */
	function create();
}

