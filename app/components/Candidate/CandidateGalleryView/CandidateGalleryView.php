<?php

namespace App\Components\Candidate;

use Doctrine\ORM\EntityManager;
use App\Model\Entity\Cv;
use App\Components\Candidate\ICandidatePreviewFactory;
use App\Components\Candidate\IMatchingControlFactory;
use App\Components\Cv\ISkillsFilterFactory;


class CandidateGalleryView extends \App\Components\BaseControl {
    
    /** @var IMatchingControlFactory @inject */
	public $matchingControlFactory;
    
    /** @var ISkillsFilterFactory @inject */
	public $skillsFilterFactory;
    
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

    public function createComponentMatchingControl() {
        $control = $this->matchingControlFactory->create();
        return $control;
    }
    
    public function createComponentSkillsFilter() {
        $control = $this->skillsFilterFactory->create();
        return $control;
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