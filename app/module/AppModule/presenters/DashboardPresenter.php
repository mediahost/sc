<?php

namespace App\AppModule\Presenters;

use App\Components\AfterRegistration\CompleteCv;
use App\Components\AfterRegistration\ICompleteCvFactory;
use App\Components\Candidate\ISocialFactory;
use App\Components\Candidate\Social;
use App\Components\Conversation\Form\ConversationList;
use App\Components\Conversation\Form\IConversationListFactory;
use App\Model\Entity\Person;
use App\Model\Entity\Role;
use App\Model\Facade\CandidateFacade;

class DashboardPresenter extends BasePresenter
{

	/** @var CandidateFacade @inject */
	public $candidateFacade;

	/** @var IConversationListFactory @inject */
	public $conversationListFactory;

	/** @var ISocialFactory @inject */
	public $socialFactory;

	/** @var ICompleteCvFactory @inject */
	public $iCompleteCvFactory;

	/**
	 * @secured
	 * @resource('dashboard')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		if ($this->user->isInRole(Role::CANDIDATE)) {
			$person = $this->user->getIdentity()->person;
			$candidate = $person->candidate;
			$this->template->person = $person;
			$this->template->candidate = $candidate;
			$this->template->invitations = $this->candidateFacade->findApprovedJobs($candidate, FALSE);
			$this->template->candidateFacade = $this->candidateFacade;
		} else if ($this->user->isInRole(Role::COMPANY)) {
			$action = $this->user->isAllowed('jobs', 'showAll') ? 'showAll' : 'default';
			$this->redirect('Jobs:' . $action);
		}
	}

	/** @return ConversationList */
	public function createComponentRecentMessages()
	{
		$sender = $this->communicationFacade->findSender($this->user->getIdentity());
		$control = $this->conversationListFactory->create();
		if ($sender) {
			$control->setSender($sender);
		}
		$control->disableSearchBox();
		return $control;
	}

	/** @return Social */
	public function createComponentSocialForm()
	{
		$control = $this->socialFactory->create()
			->setPerson($this->user->getIdentity()->person)
			->canEdit(TRUE);
		$control->onAfterSave = function (Person $saved) {
			$this->redrawControl('socialLinks');
		};
		return $control;
	}

	/** @return CompleteCv */
	public function createComponentChangeCv()
	{
		$control = $this->iCompleteCvFactory->create()
			->setCandidate($this->user->getIdentity()->person->candidate);
		$control->onAfterSave[] = function () {
			$message = $this->translator->translate('File was successfully uploaded.');
			$this->flashMessage($message, 'success');
			$this->redirect('this');
		};
		return $control;
	}

}
