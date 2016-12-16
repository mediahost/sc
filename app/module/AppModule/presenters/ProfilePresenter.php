<?php

namespace App\AppModule\Presenters;

use App\Components\AfterRegistration\CompleteCandidate;
use App\Components\AfterRegistration\CompleteCandidatePreview;
use App\Components\AfterRegistration\ICompleteCandidateFactory;
use App\Components\AfterRegistration\ICompleteCandidatePreviewFactory;
use App\Components\Candidate\Address;
use App\Components\Candidate\IAddressFactory;
use App\Components\Candidate\IPhotoFactory;
use App\Components\Candidate\IProfileFactory;
use App\Components\Candidate\ISocialFactory;
use App\Components\Candidate\Photo;
use App\Components\Candidate\Profile;
use App\Components\Candidate\Social;
use App\Components\Company\CompanyProfile;
use App\Components\Company\ICompanyProfileFactory;
use App\Components\Conversation\Form\ConversationList;
use App\Components\Conversation\Form\IConversationListFactory;
use App\Components\Cv\ISkillsFactory;
use App\Components\Cv\Skills;
use App\Components\User\CareerDocs;
use App\Components\User\ICareerDocsFactory;
use App\Model\Entity\Candidate;
use App\Model\Entity\Person;
use App\Model\Entity\Role;
use App\Model\Facade\UserFacade;

class ProfilePresenter extends BasePresenter
{

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var ISkillsFactory @inject */
	public $iSkillsFactory;

	/** @var IPhotoFactory @inject */
	public $iPhotoFactory;

	/** @var IProfileFactory @inject */
	public $iProfileFactory;

	/** @var IAddressFactory @inject */
	public $iAddressFactory;

	/** @var ISocialFactory @inject */
	public $iSocialFactory;

	/** @var ICareerDocsFactory @inject */
	public $iCareerDocsFactory;

	/** @var ICompleteCandidateFactory @inject */
	public $iCompleteCandidateFactory;

	/** @var ICompleteCandidatePreviewFactory @inject */
	public $completeCandidatePreview;

	/** @var IConversationListFactory @inject */
	public $iConversationListFactory;

	/** @var ICompanyProfileFactory @inject */
	public $companyProfileFactory;

	/** @var Person */
	private $person;

	/** @var Candidate */
	private $candidate;

	/**
	 * @secured
	 * @resource('profile')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		if (in_array(Role::ADMIN, $this->getUser()->getRoles()) || in_array(Role::SUPERADMIN, $this->getUser()->getRoles())
		) {
			$this->redirect('connectManager');
		}

		$user = $this->user->identity;
		$this->person = $user->person;
		$this->candidate = $this->person->candidate;
	}

	public function renderDefault()
	{
		$this->template->person = $this->person;
		$this->template->candidate = $this->candidate;
	}

	// <editor-fold desc="components">

	/** @return Skills */
	public function createComponentSkillsForm()
	{
		$control = $this->iSkillsFactory->create();
		$control->setTemplateFile('overview');
		$control->onlyFilledSkills = true;
		$control->setCv($this->candidate->cv);
		$control->setAjax(TRUE, TRUE);
		return $control;
	}

	/** @return Photo */
	public function createComponentPhotoForm()
	{
		$control = $this->iPhotoFactory->create();
		$control->setPerson($this->person);
		$control->onAfterSave = function (Person $saved) {
			$message = $this->translator->translate('Photo for \'%candidate%\' was successfully saved.', ['candidate' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redrawControl('personalDetails');
		};
		return $control;
	}

	/** @return Profile */
	public function createComponentProfileForm()
	{
		$control = $this->iProfileFactory->create();
		$control->setPerson($this->person);
		$control->onAfterSave = function (Person $saved) {
			$this->redrawControl('personalDetails');
		};
		return $control;
	}

	/** @return Address */
	public function createComponentAddressForm()
	{
		$control = $this->iAddressFactory->create();
		$control->setPerson($this->person);
		$control->onAfterSave = function (Person $saved) {
			$this->redrawControl('personalDetails');
		};
		return $control;
	}

	/** @return Social */
	public function createComponentSocialForm()
	{
		$control = $this->iSocialFactory->create();
		$control->setPerson($this->person);
		$control->onAfterSave = function (Person $saved) {
			$this->redrawControl('socialLinks');
		};
		return $control;
	}

	/** @return CareerDocs */
	public function createComponentDocsForm()
	{
		$control = $this->iCareerDocsFactory->create();
		$control->setCandidate($this->candidate);
		$control->setTemplateFile('overView');
		return $control;
	}

	/** @return CompleteCandidate */
	protected function createComponentCompleteCandidate()
	{
		$control = $this->iCompleteCandidateFactory->create()
			->setCandidate($this->candidate);
		$control->onSuccess[] = function (CompleteCandidate $control, Candidate $candidate) {
			$message = $this->translator->translate('Your data was saved.');
			$this->flashMessage($message, 'success');
			$this->redrawControl('interestedIn');
		};
		return $control;
	}

	/** @return CompleteCandidatePreview */
	protected function createComponentCompleteCandidatePreview()
	{
		$control = $this->completeCandidatePreview->create()
			->setCandidate($this->candidate);
		return $control;
	}

	/** @return ConversationList */
	public function createComponentRecentMessages()
	{
		$sender = $this->communicationFacade->findSender($this->person->user, $this->company);
		$control = $this->iConversationListFactory->create();
		if ($sender) {
			$control->setSender($sender);
		}
		$control->setReadMode(TRUE)
			->disableSearchBox();
		return $control;
	}

	/**  @return CompanyProfile */
	public function createComponentCompanyDetails()
	{
		$control = $this->companyProfileFactory->create();
		$control->setAjax(true, true);
		$control->setCompany($this->company);
		return $control;
	}

	// </editor-fold>
}