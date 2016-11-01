<?php

namespace App\Components\AfterRegistration;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicHorizontalFormRenderer;
use App\Model\Entity\Candidate;
use App\Model\Entity\Person;
use App\Model\Facade\JobFacade;

class CompleteCandidatePreview extends BaseControl
{

	/** @var JobFacade @inject */
	public $jobFacade;

	/** @var \Nette\Security\User @inject */
	public $user;

	/** @var Candidate */
	private $candidate;

	public function render()
	{
		$this->template->freelancer = $this->candidate->freelancer ? 'yes' : 'no';
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
		$localities = [];
		foreach (Person::getLocalities() as $group) {
			foreach ($group as $localityId => $locality) {
				if (in_array($localityId, $this->candidate->workLocations)) {
					$localities[] = $locality;
				}
			}
		}

		return [
			'categories' => implode(',', $this->candidate->jobCategoriesArray),
			'countries' => implode(',', $localities)
		];
	}

	public function setCandidate(Candidate $candidate)
	{
		$this->candidate = $candidate;
		return $this;
	}
}

interface ICompleteCandidatePreviewFactory
{

	/** @return CompleteCandidatePreview */
	function create();
}