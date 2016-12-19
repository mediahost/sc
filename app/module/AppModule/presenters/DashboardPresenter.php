<?php

namespace App\AppModule\Presenters;

use App\Components\Candidate\ISocialFactory;
use App\Components\Candidate\Social;
use App\Components\Conversation\Form\ConversationList;
use App\Components\Conversation\Form\IConversationListFactory;
use App\Components\User\CareerDocs;
use App\Components\User\ICareerDocsFactory;
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

	/** @var ICareerDocsFactory @inject */
	public $careerDocFactory;

	/**
	 * @secured
	 * @resource('dashboard')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		if ($this->user->isInRole(Role::CANDIDATE)) {
			$candidate = $this->user->getIdentity()->person->candidate;
			$this->template->candidate = $candidate;
			$this->template->invitations = $this->candidateFacade->findApprovedJobs($candidate, FALSE);
			$this->template->candidateFacade = $this->candidateFacade;
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
		$control = $this->socialFactory->create();
		$control->setPerson($this->person);
		$control->onAfterSave = function (Person $saved) {
			$this->redrawControl('socialLinks');
		};
		return $control;
	}

	/** @return CareerDocs */
	public function createComponentDocsForm()
	{
		$control = $this->careerDocFactory->create();
		$control->setCandidate($this->person->getCandidate());
		$control->setTemplateFile('overView');
		return $control;
	}

}
