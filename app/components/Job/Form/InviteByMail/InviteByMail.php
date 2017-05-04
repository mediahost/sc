<?php

namespace App\Components\Job;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Mail\Messages\IJobInvitationFactory;
use App\Model\Entity\Candidate;
use App\Model\Entity\Job;
use App\Model\Entity\Match;
use App\Model\Entity\User;
use App\Model\Facade\CandidateFacade;
use App\Model\Facade\JobFacade;
use App\Model\Facade\UserFacade;
use Nette\Mail\IMailer;
use Nette\Mail\SmtpMailer;
use Nette\Utils\ArrayHash;

class InviteByMail extends BaseControl
{

	/** @var Candidate */
	private $candidate;

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];

	/** @var array */
	public $onAfterFail = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var \Nette\Security\User @inject */
	public $user;

	/** @var IMailer @inject */
	public $mailer;

	/** @var CandidateFacade @inject */
	public $candidateFacade;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var JobFacade @inject */
	public $jobFacade;

	/** @var IJobInvitationFactory @inject */
	public $iJobInvitationFactory;

	// </editor-fold>

	protected function createComponentForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());
		if ($this->isAjax) {
			$form->getElementPrototype()->class[] = 'ajax';
		}
		if ($this->isSendOnChange) {
			$form->getElementPrototype()->class[] = 'sendOnChange';
		}

		$jobs = $this->jobFacade->getUnmatched($this->candidate, TRUE);
		$form->addSelect2('job', 'Job', $jobs)
			->setRequired('Must be filled');

		$dealers = $this->userFacade->getDealersPairs();
		$form->addSelect2('dealer', 'Sender', $dealers)
			->setRequired('Must be filled');

		$form->addText('subject', 'Subject')
			->setAttribute('placeholder', 'Job Invitation');

		$form->addWysiHtml('message', 'Invitation message')
			->addRule(Form::FILLED, 'Must be filled', NULL, 3);

		$form->addSubmit('send', 'Send');

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$userRepo = $this->em->getRepository(User::getClassName());
		$jobRepo = $this->em->getRepository(Job::getClassName());

		/** @var User $dealer */
		$dealer = $userRepo->find($values->dealer);
		$job = $jobRepo->find($values->job);
		if ($dealer && $job) {

			if ($dealer->smtpAccount && $this->mailer instanceof SmtpMailer) {
				$options = [
					'host' => $dealer->smtpAccount->host,
					'username' => $dealer->smtpAccount->username,
					'password' => $dealer->smtpAccount->password,
					'secure' => $dealer->smtpAccount->secure,
				];
				$this->mailer->setOptions($options);
			}

			$match = $this->candidateFacade->matchApprove($this->candidate, $job);

			$invitationMessage = $this->iJobInvitationFactory->create();
			$invitationMessage->setSender($dealer);
			$invitationMessage->setMatch($match);
			$invitationMessage->setText($values->message, $values->subject);
			$invitationMessage->send();

			$this->onAfterSave($match);
		} else {
			$this->onAfterFail();
		}
	}

	// <editor-fold desc="setters & getters">

	public function setCandidate(Candidate $candidate)
	{
		$this->candidate = $candidate;
		return $this;
	}

	// </editor-fold>
}

interface IInviteByMailFactory
{

	/** @return InviteByMail */
	function create();
}
