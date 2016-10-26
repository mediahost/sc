<?php

namespace App\AppModule\Presenters;

use App\Components\Candidate\ISocialFactory;
use App\Components\Conversation\Form\IConversationListFactory;
use App\Components\User\ICareerDocsFactory;
use App\Model\Entity\Person;

class DashboardPresenter extends BasePresenter
{
	/** @var IConversationListFactory @inject */
	public $conversationListFactory;

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

	/** @return \App\Components\Conversation\Form\ConversationList */
	public function createComponentRecentMessages()
	{
		$senders = $this->communicationFacade->findSenders($this->user->getIdentity());
		$control = $this->conversationListFactory->create();
		$control->setSender(current($senders))
			->disableSearchBox();
		return $control;
	}

	/** @return \App\Components\Candidate\Social */
	public function createComponentSocialForm()
	{
		$control = $this->socialFactory->create();
		$control->setPerson($this->person);
		$control->onAfterSave = function (Person $saved) {
			$this->redrawControl('socialLinks');
		};
		return $control;
	}

	/** @return \App\Components\User\CareerDocs */
	public function createComponentDocsForm()
	{
		$control = $this->careerDocFactory->create();
		$control->setCandidate($this->person->getCandidate());
		$control->setTemplateFile('overView');
		return $control;
	}
}
