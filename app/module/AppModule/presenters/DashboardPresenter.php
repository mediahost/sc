<?php

namespace App\AppModule\Presenters;

use App\Components\Candidate\IJobListFactory;
use App\Components\Candidate\ISocialFactory;
use App\Components\Candidate\JobList;
use App\Components\Candidate\Social;
use App\Components\Conversation\Form\ConversationList;
use App\Components\Conversation\Form\IConversationListFactory;
use App\Components\User\CareerDocs;
use App\Components\User\ICareerDocsFactory;
use App\Model\Entity\Person;

class DashboardPresenter extends BasePresenter
{
	/** @var IConversationListFactory @inject */
	public $conversationListFactory;

	/** @var IJobListFactory @inject */
	public $jobListFactory;

	/** @var ISocialFactory @inject */
	public $socialFactory;

	/** @var ICareerDocsFactory @inject */
	public $careerDocFactory;

	/** @var Person */
	private $person;

	protected function startup()
	{
		parent::startup();
		$this->person = $this->user->identity->person;
	}

	/**
	 * @secured
	 * @resource('dashboard')
	 * @privilege('default')
	 */
	public function actionDefault()
	{

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

	/** @return JobList */
	public function createComponentJobList()
	{
		$control = $this->jobListFactory->create();
		$control->setLimit(5);
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
