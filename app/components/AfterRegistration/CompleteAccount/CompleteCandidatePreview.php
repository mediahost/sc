<?php

namespace App\Components\AfterRegistration;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicHorizontalFormRenderer;
use App\Model\Entity\Candidate;
use App\Model\Entity\User;
use App\Model\Facade\JobFacade;

class CompleteCandidatePreview extends BaseControl
{

	/** @var JobFacade @inject */
	public $jobFacade;

	/** @var User */
	private $userEntity;

	public function render()
	{
		$this->template->freelancer = $this->userEntity->candidate->freelancer ? 'yes' : 'no';
		$this->setTemplateFile('candidateSecondPreview');
		parent::render();
	}

	protected function createComponentForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicHorizontalFormRenderer());

		$form->addText('categories', '')
			->setAttribute('data-role', 'tagsinput')
			->setDisabled();
		$form->addText('countries', '')
			->setAttribute('data-role', 'tagsinput')
			->setDisabled();

		$form->setDefaults($this->getDefaults());
		return $form;
	}

	public function getDefaults()
	{
		$candidate = $this->userEntity->candidate;
		$categories = $this->jobFacade->findCandidatePreferedCategories($candidate);

		$localities = [];
		foreach (Candidate::getLocalities() as $group) {
			foreach ($group as $localityId => $locality) {
				if (in_array($localityId, $this->userEntity->candidate->workLocations)) {
					$localities[] = $locality;
				}
			}
		}

		return [
			'categories' => implode(',', $categories),
			'countries' => implode(',', $localities)
		];
	}

	public function setUserEntity(User $user)
	{
		$this->userEntity = $user;
	}
}

interface ICompleteCandidatePreviewFactory
{

	/** @return CompleteCandidatePreview */
	function create();
}