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
use App\Components\Candidate\IProfileIdFactory;
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
use App\Model\Entity\Cv;
use App\Model\Entity\Person;
use App\Model\Entity\Role;
use App\Model\Entity\User;
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

	/** @var IProfileIdFactory @inject */
	public $profileIdFactory;

	/** @var Person */
	private $person;

	/** @var Candidate */
	private $candidate;

	/** @var bool */
	private $isMine;

	/** @var bool */
	private $canEdit;

	public function startup()
	{
		if (!$this->user->isLoggedIn() && $this->action === 'default') {
			$this->redirect(':Front:Profile:', ['id' => $this->getParameter('id')]);
		}
		parent::startup();
	}

	public function actionUser($id)
	{
		$userRepo = $this->em->getRepository(User::getClassName());
		$user = $userRepo->find($id);
		if ($user && $user->person->candidate->id && $user->person->candidate->profileId) {
			$this->redirect('default', $user->person->candidate->profileId);
		} else {
			$message = $this->translator->translate('Candidate wasn\'t found');
			$this->flashMessage($message, 'warning');
			$this->redirect('Dashboard:');
		}
	}

	/**
	 * @secured
	 * @resource('profile')
	 * @privilege('default')
	 */
	public function actionDefault($id = NULL)
	{
		if (!$id) {
			$user = $this->user->identity;
			$this->person = $user->person;
			if ($this->user->isInRole(Role::CANDIDATE)) {
				$this->candidate = $this->person->candidate;
			}
			if ($this->candidate && $this->candidate->id && $this->candidate->profileId) {
				$this->redirect('this', $this->candidate->profileId);
			}
		} else {
			$candidateRepo = $this->em->getRepository(Candidate::getClassName());
			$this->candidate = $candidateRepo->findOneByProfileId($id);
			if ($this->candidate) {
				$this->person = $this->candidate->person;
			} else {
				$message = $this->translator->translate('Candidate wasn\'t found');
				$this->flashMessage($message, 'warning');
				$this->redirect('Dashboard:');
			}
		}

		if (!$this->candidate->cv->id) {
			$cvRepo = $this->em->getRepository(Cv::getClassName());
			$cvRepo->save($this->candidate->cv);
		}

		$this->isMine = $this->user->id === $this->person->user->id;
		$this->canEdit = $this->isMine || $this->user->isAllowed('profile', 'edit-others');
	}

	public function renderDefault()
	{
		$this->template->isMine = $this->isMine;
		$this->template->canEdit = $this->canEdit;
		$this->template->person = $this->person;
		$this->template->candidate = $this->candidate;
	}

	// <editor-fold desc="components">

	/** @return Skills */
	public function createComponentSkillsForm()
	{
		$control = $this->iSkillsFactory->create()
			->canEdit($this->canEdit)
			->setCv($this->candidate->cv)
			->setTemplateFile('overview')
			->setAjax(TRUE, TRUE);
		$control->onlyFilledSkills = TRUE;
		return $control;
	}

	/** @return Photo */
	public function createComponentPhotoForm()
	{
		$control = $this->iPhotoFactory->create()
			->setPerson($this->person)
			->canEdit($this->canEdit);
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
		$control = $this->iProfileFactory->create()
			->setPerson($this->person)
			->canEdit($this->canEdit);
		$control->onAfterSave = function (Person $saved) {
			$this->redrawControl('personalDetails');
		};
		return $control;
	}

	/** @return Address */
	public function createComponentAddressForm()
	{
		$control = $this->iAddressFactory->create()
			->setPerson($this->person)
			->canEdit($this->canEdit);
		$control->onAfterSave = function (Person $saved) {
			$this->redrawControl('personalDetails');
		};
		return $control;
	}

	/** @return Social */
	public function createComponentSocialForm()
	{
		$control = $this->iSocialFactory->create()
			->setPerson($this->person)
			->canEdit($this->canEdit);
		$control->onAfterSave = function (Person $saved) {
			$this->redrawControl('socialLinks');
		};
		return $control;
	}

	/** @return CareerDocs */
	public function createComponentDocsForm()
	{
		$control = $this->iCareerDocsFactory->create()
			->setCandidate($this->candidate)
			->canEdit($this->canEdit)
			->setTemplateFile('overView');
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

	public function createComponentProfileId()
	{
		$control = $this->profileIdFactory->create()
			->setCandidate($this->candidate)
			->canEdit($this->canEdit);
		$control->onAfterSave[] = function (Candidate $candidate) {
			$this->redirect('this', ['id' => $candidate->profileId]);
		};
		return $control;
	}

	// </editor-fold>
}