<?php

namespace App\Components\AfterRegistration;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicHorizontalFormRenderer;
use App\Model\Entity\Candidate;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillCategory;
use App\Model\Entity\User;
use App\Model\Facade\SkillFacade;
use App\Model\Facade\JobFacade;
use App\Model\Facade\UserFacade;
use Nette\Utils\ArrayHash;
use Nette\Utils\Random;
use Tracy\Debugger;

class CompleteCandidateSecondControl extends BaseControl
{

	// <editor-fold desc="events">

	/** @var array */
	public $onSuccess = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var SkillFacade @inject */
	public $skillFacade;
    
    /** @var JobFacade @inject */
	public $jobFacade;

	/** @var \Nette\Security\User @inject */
	public $user;
    
    /** @var \App\Model\Entity\User */
    private $userEntity;

	// </editor-fold>

    public function render()
	{
		$skillRepo = $this->em->getRepository(Skill::getClassName());

		$this->setTemplateFile('candidateSecond');
        
        $jsonJobCategories = [];
        $jobCategories = $this->jobFacade->findTopCategories();
        foreach ($jobCategories as $category) {
			$jsonJobCategories[] = $this->jobCategoryToLeaf($category);
		}
        
		$jsonLocalities = [];
		foreach (Candidate::getLocalities() as $localityId => $locality) {
			$jsonLocalities[] = $this->loacationToLeaf($localityId, $locality);
		}

        $this->template->jobCategories = $this->jobFacade->findCategoriesPairs();
        $this->template->jsonJobCategories = $jsonJobCategories;

		$this->template->countries = Candidate::getLocalities(TRUE);
		$this->template->jsonCountries = $jsonLocalities;

		$this->template->jsonFreelancer = [
			'id' => 'for-frmpreferencesForm-freelancer',
			'text' => 'Yes',
			'state' => [
				'selected' => $this->userEntity->candidate->freelancer,
			],
		];
		parent::render();
	}
    
	protected function createComponentForm()
	{
		$form = new Form();
		$form->setRenderer(new MetronicHorizontalFormRenderer());
		$form->setTranslator($this->translator);
        $form->getElementPrototype()->class('ajax');
        
        $categories = $this->jobFacade->findCategoriesPairs();
        $categoriesContainer = $form->addContainer('categories');
        foreach ($categories as $categoryId => $categoryName) {
			$categoriesContainer->addCheckbox($categoryId, $categoryName)
				->setAttribute('class', 'inCategoryTree');
		}

		$countryContainer = $form->addContainer('countries');
		foreach (Candidate::getLocalities(TRUE) as $countryId => $countryName) {
			$countryContainer->addCheckbox($countryId, $countryName)
				->setAttribute('class', 'inCountryTree');
		}

		$form->addCheckbox('freelancer', 'I am also interested in freelance or remote work');

		$form->addSubmit('save', 'Save');

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$userRepo = $this->em->getRepository(User::getClassName());

		$user = $this->userEntity;
        
        $categoryList = [];
        foreach ($values->categories as $categoryId => $checked) {
			if ($checked) {
				$categoryList[$categoryId] = $categoryId;
			}
		}
		if (!count($categoryList)) {
			$form->addError('Enter at least one category');
		}

		$countryList = [];
		foreach ($values->countries as $countryId => $checked) {
			if ($checked) {
				$countryList[$countryId] = $countryId;
			}
		}
		if (!count($countryList)) {
			$form->addError('Enter at least one country');
		}
		
        $user->candidate->jobCategories = $categoryList;
		$user->candidate->workLocations = $countryList;
		$user->candidate->freelancer = $values->freelancer;

		$userRepo->save($user);

		if (!$form->hasErrors()) {

			$this->onSuccess($this, $user->candidate);
		}
	}
    
    private function jobCategoryToLeaf(\App\Model\Entity\JobCategory $category)
	{
		$leaf = [
			'id' => $category->id,
			'text' => $category->name,
		];
		$children = [];
		foreach ($category->childs as $child) {
			$children[] = $this->jobCategoryToLeaf($child);
		}
        $leaf['state'] = [
			'selected' => in_array($category->id, $this->userEntity->candidate->jobCategories),
		];
		$leaf['children'] = $children;
		return $leaf;
	}

	private function loacationToLeaf($id, $location)
	{
		$children = [];
		if (is_array($location)) {
			$leaf = [
				'text' => $id,
			];
			foreach ($location as $localityId => $localityName) {
				$children[] = $this->loacationToLeaf($localityId, $localityName);
			}
		} else {
			$leaf = [
				'id' => $id,
				'text' => $location,
			];
		}
		$leaf['state'] = [
			'selected' => in_array($id, $this->userEntity->candidate->workLocations),
		];
		$leaf['children'] = $children;
		return $leaf;
	}

    public function setUserEntity(User $user) {
        $this->userEntity = $user;
    }
}

interface ICompleteCandidateSecondControlFactory
{

	/** @return CompleteCandidateSecondControl */
	function create();
}
