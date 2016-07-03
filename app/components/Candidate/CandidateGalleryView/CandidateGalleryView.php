<?php

namespace App\Components\Candidate;

use Doctrine\ORM\EntityManager;
use App\Model\Entity\Cv;
use App\Components\Candidate\ICandidatePreviewFactory;


class CandidateGalleryView extends \App\Components\BaseControl {
    
    /** @var ICandidatePreviewFactory @inject */
	public $candidatePreviewFactory;
    
    /** @var EntityManager @inject */
	public $em;
    
    /** @var SkillKnowRequest[] */
	private $skillRequests = [];
    
    /** @var Cv[] */
	private $cvs = [];
    
    


    /**
     * Renders control
     */
    public function render() {
        $this->setTemplateFile('CandidateGalleryView');
        $this->cvs = $this->getCvs();
        $this->template->cvs = $this->cvs;
        parent::render();
    }

    private function getCvs()
	{
		$cvRepo = $this->em->getRepository(Cv::getClassName());

		if (count($this->skillRequests)) {
			return $cvRepo->findBySkillRequests($this->skillRequests);
		}

		return $cvRepo->findAll();
	}


    public function createComponentCandidatePreview() {
        return new \Nette\Application\UI\Multiplier(function ($cvId) {
            $cv = \App\ArrayUtils::searchByProperty($this->cvs, 'id', $cvId);
            $control = new CandidatePreview();
            $control->setCv($cv);
            return $control;
        });
    }
}

interface ICandidateGalleryViewFactory
{

	/** @return CandidateGalleryView */
	function create();
}