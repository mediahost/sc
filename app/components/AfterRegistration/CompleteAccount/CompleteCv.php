<?php

namespace App\Components\AfterRegistration;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Candidate;
use Nette\Utils\ArrayHash;

class CompleteCv extends BaseControl
{

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var Candidate */
	private $candidate;

	// </editor-fold>

	public function render()
	{
		$this->setTemplateFile('cv');
		$this->template->candidate = $this->candidate;
		parent::render();
	}

	protected function createComponentForm()
	{
		$form = new Form();
		$form->setRenderer(new Bootstrap3FormRenderer());
		$form->setTranslator($this->translator);

		$acceptedFiles = [
			'application/pdf',
			'application/msword',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		];
		$form->addUpload('cvFile', 'Upload New CV')
			->addRule(Form::MIME_TYPE, 'File must be PDF or DOC', implode(',', $acceptedFiles));

		$form->addHidden('jobApplyId');
		$form->addHidden('redirectUrl');

		$form->addSubmit('send', 'Upload');

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$this->candidate->cvFile = $values->cvFile;

		$candidateRepo = $this->em->getRepository(Candidate::getClassName());
		$candidateRepo->save($this->candidate);

		$this->onAfterSave($this, $this->candidate, $values->jobApplyId, $values->redirectUrl);
	}

	public function setCandidate(Candidate $candidate)
	{
		$this->candidate = $candidate;
		return $this;
	}
}

interface ICompleteCvFactory
{
	/** @return CompleteCv */
	public function create();
}