<?php

namespace App\Components\Candidate;

use Doctrine\ORM\EntityManager;
use App\Model\Entity\Cv;
use App\Components\Candidate\ICandidatePreviewFactory;
use App\Components\Candidate\IMatchingControlFactory;
use App\Components\Cv\ISkillsFilterFactory;


class CandidateGalleryView extends \App\Components\BaseControl {
    
    static $pagination = [6, 12, 18];

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
    
    /** @var int */
    private $countPerPage;
    
    /** @var int */
    private $current = 1;
    
    
    public function __construct() {
        parent::__construct();
        $this->countPerPage = self::$pagination[2];
    }

    /**
     * Renders control
     */
    public function render() {
        $this->setTemplateFile('CandidateGalleryView');
        $this->cvs = $this->getCvs();
        $this->template->pageParams = $this->getPagination();
        $this->template->cvs = $this->groupCvs($this->cvs);
        parent::render();
    }
    
    public function handlePagination($page) {
        $this->current = $page;
    }
    
    public function handleChangePagination($count) {
        $this->countPerPage = $count;
    }
    
    public function handleResetFilter() {
        $this->skillRequests = [];
        $this['skillsFilter']->setSkillRequests([]);
        $this->redrawControl();
    }
    
    private function getPagination() {
        $cvRep = $this->em->getRepository(Cv::getClassName());
        $count = $cvRep->countOfCvs($this->skillRequests);
        $pages = \App\Helpers::pagination($count, $this->countPerPage, $this->current , 4);
        $last = ceil($count/$this->countPerPage);
        $parameters = [
            'current' => $this->current,
            'pages' => $pages,
            'last' => $last,
            'availableCounts' => array_diff(self::$pagination, [$this->countPerPage]),
            'countPerPage' => $this->countPerPage
        ];
        if ($this->current > 1) {
            $parameters['previous'] = $this->current-1; 
        }
        if ($this->current < $last) {
            $parameters['next'] = $this->current + 1; 
        }
        return $parameters;
    }

    private function getCvs()
	{
        $cvRep = $this->em->getRepository(Cv::getClassName());
        $offset = $this->countPerPage*($this->current - 1);
		return $cvRep->findBySkillRequests($this->skillRequests, $offset, $this->countPerPage);
	}
    
    private function groupCvs($cvs) {
        $group = [];
        $groups = [];
        foreach ($cvs as $cv) {
            if (count($group) == 3) {
                $groups[] = $group;
                $group = [];
            }
            $group[] = $cv;
        }
        $groups[] = $group;
        return $groups;
    }
    
    public function setSkillRequests($skillRequests)
	{
		foreach ($skillRequests as $id => $skillRequest) {
			$this->skillRequests[$id] = $skillRequest;
		}
		return $this;
	}

    public function createComponentMatchingControl() {
        $control = $this->matchingControlFactory->create();
        return $control;
    }
    
    public function createComponentSkillsFilter() {
        $control = $this->skillsFilterFactory->create();
        $control->setAjax();
        $control->onAfterSend = function (array $skillRequests) {
			$this->setSkillRequests($skillRequests);
			$this->redrawControl();
		};
        return $control;
    }

    public function createComponentCandidatePreview() {
        return new \Nette\Application\UI\Multiplier(function ($cvId) {
            $cv = \App\ArrayUtils::searchByProperty($this->cvs, 'id', $cvId);
            $control = $this->candidatePreviewFactory->create();
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