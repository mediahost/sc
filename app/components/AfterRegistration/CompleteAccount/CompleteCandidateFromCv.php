<?php

namespace App\Components\AfterRegistration;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicHorizontalFormRenderer;
use App\Model\Entity\Candidate;
use App\Model\Entity\Person;
use App\Model\Entity\User;
use Nette\Forms\IControl;
use Nette\Utils\ArrayHash;

class CompleteCandidateFromCv extends BaseControl
{
	/** @var \Nette\Security\User @inject */
	public $user;

	public function render()
	{
		$this->setTemplateFile('candidateFromCv');
		parent::render();
	}

	protected function createComponentForm()
	{
		$form = new Form();
		$form->setRenderer(new MetronicHorizontalFormRenderer());
		$form->setTranslator($this->translator);

		$form->addGroup('Cv file');
		$form->addUpload('cvFile', 'Cv')->addRule([$this, 'validateFileType'], 'Wrong file type!');
		$form->addSubmit('save', 'Continue');
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$userRepo = $this->em->getRepository(User::getClassName());
		$user = $this->user->getIdentity();
		$candidate = $user->getPerson()->getCandidate();
		$candidate->cvFile = $values->cvFile;
		$userRepo->save($user, $candidate);
	}

	public function validateFileType(IControl $control)
	{
		$ext = strtolower(pathinfo($control->value->getName(), PATHINFO_EXTENSION));
		return in_array($ext, ['pdf', 'doc']);
	}
}

interface ICompleteCandidateFromCvFactory
{
	/** @return CompleteCandidateFromCv */
	public function create();
}