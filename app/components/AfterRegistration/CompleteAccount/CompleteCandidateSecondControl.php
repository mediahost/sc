<?php

namespace App\Components\AfterRegistration;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicHorizontalFormRenderer;
use App\Model\Entity\Address;
use App\Model\Entity\Candidate;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillCategory;
use App\Model\Entity\User;
use App\Model\Facade\SkillFacade;
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

	/** @var \Nette\Security\User @inject */
	public $user;
    
    /** @var \App\Model\Entity\User */
    private $userEntity;

	// </editor-fold>

	public function render()
	{
		$skillRepo = $this->em->getRepository(Skill::getClassName());

		$this->setTemplateFile('candidateSecond');

		$jsonSkills = [];
		$categories = $this->skillFacade->getTopCategories();
		foreach ($categories as $category) {
			$jsonSkills[] = $this->categoryToLeaf($category);
		}
		$jsonLocalities = [];
		foreach (Candidate::getLocalities() as $localityId => $locality) {
			$jsonLocalities[] = $this->loacationToLeaf($localityId, $locality);
		}

		$this->template->skills = $skillRepo->findPairs('name');;
		$this->template->jsonSkills = $jsonSkills;

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

		$skillRepo = $this->em->getRepository(Skill::getClassName());
		$skills = $skillRepo->findPairs('name');
		$skillsContainer = $form->addContainer('skills');
		foreach ($skills as $skillId => $skillName) {
			$skillsContainer->addCheckbox($skillId, $skillName)
				->setAttribute('class', 'inSkillTree');
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

		$skillList = [];
		foreach ($values->skills as $skillId => $checked) {
			if ($checked) {
				$skillList[$skillId] = $skillId;
			}
		}
		if (!count($skillList)) {
			$form->addError('Enter at least one skill');
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
		
		$user->candidate->qualifiedSkills = $skillList;
		$user->candidate->workLocations = $countryList;
		$user->candidate->freelancer = $values->freelancer;

		$userRepo->save($user);

		if (!$form->hasErrors()) {

			$this->onSuccess($this, $user->candidate);
		}
	}

	private function categoryToLeaf(SkillCategory $category)
	{
		$leaf = [
			'id' => 'c-' . $category->id,
			'text' => $category->name,
		];
		$children = [];
		foreach ($category->childs as $child) {
			$children[] = $this->categoryToLeaf($child);
		}
		foreach ($category->skills as $skill) {
			$children[] = [
				'id' => $skill->id,
				'text' => $skill->name,
				'state' => [
					'selected' => in_array($skill->id, $this->userEntity->candidate->qualifiedSkills),
				],
			];
		}
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
