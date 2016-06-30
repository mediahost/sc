<?php

namespace App\Components\AfterRegistration;

use App\Forms\Renderers\MetronicHorizontalFormRenderer;
use App\Model\Entity\User;
use App\Model\Facade\JobFacade;


class CompleteCandidatePreview extends \App\Components\BaseControl {
    
    /** @var JobFacade @inject */
	public $jobFacade;
    
    /** @var \App\Model\Entity\User */
    private $userEntity;
    
    
    public function render() {
        $this->setTemplateFile('candidateSecondPreview');
        parent::render();
    }
    
    protected function createComponentForm() {
        $form = new \App\Forms\Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicHorizontalFormRenderer);
		
		$form->addText('categories', '')
			->setAttribute('data-role', 'tagsinput')
            ->setDisabled();
		$form->addText('countries', '')
			->setAttribute('data-role', 'tagsinput')
            ->setDisabled();
        
        $form->addCheckbox('freelance')->setDisabled();

		$form->setDefaults($this->getDefaults());
		return $form;
    }
    
    public function getDefaults() {
        $categories = [];
        $allCategories = $this->jobFacade->findCategoriesPairs();
        foreach ($allCategories as $id => $category) {
            if (in_array($id, $this->userEntity->candidate->jobCategories)) {
                $categories[] = $category;
            }
        }
        
        $localities = [];
        foreach (\App\Model\Entity\Candidate::getLocalities() as $group) {
            foreach ($group as $localityId => $locality) {
                if (in_array($localityId, $this->userEntity->candidate->workLocations)) {
                    $localities[] = $locality;
                }
            }
		}
        
        return [
			'categories' => implode(',', $categories),
			'countries' => implode(',', $localities),
            'freelance' => $this->userEntity->candidate->freelancer
		];
    }
    
    public function setUserEntity(User $user) {
        $this->userEntity = $user;
    }
}

interface ICompleteCandidatePreviewFactory
{

	/** @return CompleteCandidatePreview */
	function create();
}