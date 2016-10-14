<?php

namespace App\Components\AfterRegistration;

use App\Components\BaseControl;
use App\Forms\Form;

class CompleteCandidateFromCv extends BaseControl
{

	public function render()
	{
		$this->setTemplateFile('candidateFromCv');
		parent::render();
	}

	protected function createComponentForm()
	{
		$form = new Form();
		return $form;
	}
}

interface ICompleteCandidateFromCvFactory
{
	/** @return CompleteCandidateFromCv */
	public function create();
}