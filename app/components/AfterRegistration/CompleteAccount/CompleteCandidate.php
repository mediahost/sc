<?php

namespace App\Components\AfterRegistration;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicHorizontalFormRenderer;
use App\Model\Entity\Candidate;
use App\Model\Entity\JobCategory;
use App\Model\Entity\Person;
use App\Model\Entity\User;
use App\Model\Facade\JobFacade;
use App\Model\Facade\SkillFacade;
use App\Model\Facade\UserFacade;
use Nette\Utils\ArrayHash;

class CompleteCandidate extends BaseControl
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

	/** @var Candidate */
	private $candidate;

	// </editor-fold>

	public function render()
	{
		$this->setTemplateFile('candidate');

		$jsonJobCategories = [];
		$jobCategories = $this->jobFacade->findTopCategories();
		foreach ($jobCategories as $category) {
			$jsonJobCategories[] = $this->jobCategoryToLeaf($category);
		}

		$jsonLocalities = [];
		foreach (Person::getLocalities() as $localityId => $locality) {
			$jsonLocalities[] = $this->loacationToLeaf($localityId, $locality);
		}

		$candidate = isset($this->candidate)  ?  $this->candidate  :  $this->user->getIdentity()->getCandidate();

		$this->template->jobCategories = $this->jobFacade->findCategoriesPairs();
		$this->template->jsonJobCategories = $jsonJobCategories;

		$this->template->countries = Person::getLocalities(TRUE);
		$this->template->jsonCountries = $jsonLocalities;

		$this->template->jsonFreelancer = [
			'id' => 'for-frmpreferencesForm-freelancer',
			'text' => 'Yes',
			'state' => [
				'selected' => $candidate->freelancer,
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
		foreach (Person::getLocalities(TRUE) as $countryId => $countryName) {
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

		$user = $this->user->getIdentity();
		$candidate = isset($this->candidate)  ?  $this->candidate  :  $user->getCandidate();

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

		$candidate->jobCategories = $categoryList;
		$candidate->workLocations = $countryList;
		$candidate->freelancer = $values->freelancer;

		$userRepo->save($user);

		if (!$form->hasErrors()) {
			$this->onSuccess($this, $candidate);
		}
	}

	private function jobCategoryToLeaf(JobCategory $category)
	{
		$candidate = isset($this->candidate)  ?  $this->candidate  :  $this->user->getIdentity()->getCandidate();
		$leaf = [
			'id' => $category->id,
			'text' => $category->name,
		];
		$children = [];
		foreach ($category->childs as $child) {
			$children[] = $this->jobCategoryToLeaf($child);
		}
		$leaf['state'] = [
			'selected' => in_array($category->id, $candidate->jobCategories),
		];
		$leaf['children'] = $children;
		return $leaf;
	}

	private function loacationToLeaf($id, $location)
	{
		$candidate = isset($this->candidate)  ?  $this->candidate  :  $this->user->getIdentity()->getCandidate();
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
			'selected' => in_array($id, $candidate->workLocations),
		];
		$leaf['children'] = $children;
		return $leaf;
	}

	public function setCandidate(Candidate $candidate)
	{
		$this->candidate = $candidate;
		return $this;
	}
}

interface ICompleteCandidateFactory
{

	/** @return CompleteCandidate */
	function create();
}
