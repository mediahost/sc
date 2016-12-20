<?php

namespace App\FrontModule\Presenters;

use App\Components\Auth\Facebook;
use App\Components\Auth\IFacebookFactory;
use App\Components\Auth\ILinkedinFactory;
use App\Components\Auth\ISignInFactory;
use App\Components\Auth\Linkedin;
use App\Components\Auth\SignIn;
use App\Model\Entity\Job;
use Kdyby\Doctrine\EntityManager;

class JobPresenter extends BasePresenter
{

	/** @var Job */
	private $job;

	/** @var EntityManager @inject */
	public $em;

	/** @var ISignInFactory @inject */
	public $iSignInFactory;

	/** @var IFacebookFactory @inject */
	public $iFacebookFactory;

	/** @var ILinkedinFactory @inject */
	public $iLinkedinFactory;

	protected function beforeRender()
	{
		$this->setLayout('supr');
		parent::beforeRender();
	}

	// <editor-fold desc="actions & renderers">

	public function actionView($id)
	{
		if ($this->user->isLoggedIn()) {
			$this->redirect(':App:Job:view', $id);
		}

		$jobRepo = $this->em->getRepository(Job::getClassName());
		$this->job = $jobRepo->find($id);
		if (!$this->job) {
			$message = $this->translator->translate('Finded job isn\'t exists.');
			$this->flashMessage($message, 'danger');
			$this->redirect('Homepage:');
		} else {
			$this['signIn']->setJobApply($this->job);
//			$this['facebookApply']->setJobApply($this->job);
//			$this['linkedinApply']->setJobApply($this->job);
			$this['signIn']->setRedirectUrl($this->link('//this'));
//			$this['facebookApply']->setRedirectUrl($this->link('//this'));
//			$this['linkedinApply']->setRedirectUrl($this->link('//this'));
		}
	}

	public function renderView()
	{
		$this->template->job = $this->job;
	}

	// </editor-fold>
	// <editor-fold desc="forms">

	/** @return SignIn */
	protected function createComponentSignIn()
	{
		$control = $this->iSignInFactory->create();
		return $control;
	}

	/** @return Facebook */
	protected function createComponentFacebook()
	{
		$control = $this->iFacebookFactory->create();
		return $control;
	}

	/** @return Linkedin */
	protected function createComponentLinkedin()
	{
		$control = $this->iLinkedinFactory->create();
		return $control;
	}

	// </editor-fold>

}
