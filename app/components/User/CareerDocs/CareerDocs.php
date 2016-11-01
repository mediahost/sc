<?php

namespace App\Components\User;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Candidate;
use App\Model\Entity\Document;
use Doctrine\ORM\EntityManager;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

class CareerDocs extends BaseControl
{
	/** @var array */
	public $onAfterSave = [];

	/** @var EntityManager @inject */
	public $em;

	/** @var User @inject */
	public $user;

	/** @var Candidate */
	private $candidate;

	/** @var bool */
	private $isSameUser;

	public function render()
	{
		$this->template->candidate = $this->candidate;
		$this->template->isSameUser = $this->isSameUser;
		parent::render();
	}

	public function createComponentForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());
		$form->getElementPrototype()->setClass('dropzone dz-clickable dz-started');
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$this->load($form);
		$this->save();
		$this->onAfterSave($this->candidate);
	}

	protected function load(Form $form)
	{
		$file = $form->getHttpData()['file'];
		$document = new Document();
		$document->setFile($file);
		$document->public = true;
		$this->candidate->addDocument($document);
	}

	protected function save()
	{
		$this->em->persist($this->candidate);
		$this->em->flush();
		return $this;
	}

	public function getUploadedDocs()
	{
		$docs = [];
		foreach ($this->candidate->getDocuments() as $document) {
			$docs[] = $document;
		}
		return $docs;
	}

	public function handleDeleteDoc($id)
	{
		$rep = $this->em->getRepository(Document::getClassName());
		$doc = $rep->find($id);
		if ($doc) {
			$this->em->remove($doc);
			$this->em->flush();
			$this->onAfterSave();
		}
	}

	public function handleDeleteAll()
	{
		foreach ($this->candidate->getDocuments() as $doc) {
			$this->em->remove($doc);
		}
		$this->em->flush();
		$this->onAfterSave();
	}

	public function handlePublic($id)
	{
		$rep = $this->em->getRepository(Document::getClassName());
		$doc = $rep->find($id);
		$doc->public = !$doc->public;
		$rep->save($doc);
	}

	public function setCandidate(Candidate $candidate)
	{
		$this->candidate = $candidate;
		$this->isSameUser = $candidate->person->user->id && $this->user->id;
		return $this;
	}

	public function setTemplateFile($name)
	{
		return parent::setTemplateFile($name);
	}
}


Interface ICareerDocsFactory
{

	/** @return CareerDocs */
	public function create();
}