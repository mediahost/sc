<?php

namespace App\Components\User;
use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Candidate;
use App\Model\Entity\Document;
use Doctrine\ORM\EntityManager;

/**
 * Class CareerDocs
 * @package App\Components\User
 */
class CareerDocsControl extends BaseControl
{
    /** @var array */
    public $onAfterSave = [];

    /** @var EntityManager @inject */
    public $em;

    /** @var DocStorage */
    private $docStorage;

    /** @var Candidate */
    private $candidate;


    /**
     * CareerDocsControl constructor.
     * @param DocStorage $docStorage
     */
    public function __construct(DocStorage $docStorage)
    {
        $this->docStorage = $docStorage;
    }

    /**
     * @return Form
     */
    public function createComponentForm() {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->setRenderer(new Bootstrap3FormRenderer());
        $form->getElementPrototype()->setClass('dropzone dz-clickable dz-started');
        $form->onSuccess[] = $this->handleSend;
        return $form;
    }

    /**
     * @param Form $form
     * @param $values
     */
    public function handleSend(Form $form, $values) {
        $file = $form->getHttpData()['file'];
        try {
            $file = $this->docStorage->upload($file, $fileName);
            $doc = new Document($fileName);
            $doc->candidate = $this->candidate;
            $doc->public = true;
            $rep = $this->em->getDao(Document::getClassName());
            $rep->save($doc);
            $this->onAfterSave();
        } catch (CareerDocsException $ex) {
            $form->addError('Document is not uploaded');
        }
    }

    public function getUploadedDocs() {
        $docs = [];
        foreach ($this->candidate->getDocuments() as $document) {
            $docs[] = $document;
        }
        return $docs;
    }
    
    public function handleDeleteDoc($id) {
        $rep = $this->em->getDao(Document::getClassName());
        $doc = $rep->find($id);
        $this->docStorage->removeFile($doc->name);
        $rep->delete($doc);
        $this->onAfterSave();
    }

    public function handleDeleteAll() {
        $rep = $this->em->getDao(Document::getClassName());
        foreach ($this->candidate->getDocuments() as $doc) {
            $this->docStorage->removeFile($doc->name);
            $rep->delete($doc);
        }
        $this->onAfterSave();
    }
    
    public function handleSwitch($id) {
        $rep = $this->em->getDao(Document::getClassName());
        $doc = $rep->find($id);
        $doc->public = !$doc->public;
        $rep->save($doc);
    }

    /**
     * setter for $candidate
     * @param Candidate $candidate
     */
    public function setCandidate(Candidate $candidate) {
        $this->candidate = $candidate;
    }

    public function setTemplateFile($name)
    {
        return parent::setTemplateFile($name);
    }

    public function fullDocName($name) {
        return $this->docStorage->getFullName($name);
    }

    public function displayDocName($name) {
        return $this->docStorage->getDisplayName($name);
    }

    public function fileExtension($name) {
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        switch(strtolower($ext)) {
            case 'jpg': return 'jpg';
            case 'png': return 'png';
            case 'pdf': return 'pdf';
            default: return 'default';
        }
    }
}


Interface ICareerDocsControlFactory
{
    /** @return CareerDocsControl */
    public function create();
}