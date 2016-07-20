<?php

namespace App\Extensions\Installer\Model;

use App\Model\Facade\UserFacade;
use App\Model\Facade\JobFacade;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\CvFacade;
use Kdyby\Doctrine\EntityManager;


Class CandidatesGenerator extends \Nette\Object
{
    const CANDIDATE_COUNT = 5000;
    
    /** @var EntityManager @inject */
	public $em;
    
    /** @var UserFacade @inject */
	public $userFacade;
    
    /** @var JobFacade @inject */
	public $jobFacade;
    
    /** @var RoleFacade @inject */
	public $roleFacade;
    
    /** @var CvFacade @inject */
	public $cvFacade;
    
    
    public function generate() {
        $role = $this->roleFacade->findByName(\App\Model\Entity\Role::CANDIDATE);
        for($i=0; $i<self::CANDIDATE_COUNT; $i++) {
            $userMail = sprintf('candidate%d@example.com', $i + 2000);
            $pass = 'candidate123';
            $user = $this->userFacade->create($userMail, $pass, $role);
            
            $user->candidate->surname = sprintf('candidate%d', $i + 2000);
            $user->candidate->freelancer = rand(0,1);
            $user->candidate->jobCategories = $this->generateCategoryList();
            $user->candidate->workLocations = $this->generateLocationList();
            $userRepo = $this->em->getRepository(\App\Model\Entity\User::getClassName());
            $userRepo->save($user);
            $this->cvFacade->create($user->candidate);
        }
    }
    
    public function generateCategoryList() {
        $categories = $this->jobFacade->findCategoriesPairs();
        $categoryList = [];
        $count = rand(0, count($categories));
        for ($i=0; $i<$count; $i++) {
            $key = rand(0, count($categories));
            $categoryList[$key] = $key;
        }
        return $categories;
    }
    
    public function generateLocationList() {
        $countries = \App\Model\Entity\Candidate::getLocalities(TRUE);
        $countryList = [];
        $count = rand(0, count($countries));
        for ($i=0; $i<$count; $i++) {
            $key = rand(0, count($countries));
            $countryList[$key] = $key;
        }
        return $countryList;
    }
}
