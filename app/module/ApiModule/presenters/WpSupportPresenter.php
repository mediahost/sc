<?php

namespace App\ApiModule\Presenters;

use App\Components\Auth\IFacebookFactory;
use App\Components\Auth\ILinkedinFactory;
use App\Model\Entity\Candidate;
use App\Model\Entity\Job;
use App\Model\Entity\Match;
use App\Model\Entity\User;

class WpSupportPresenter extends BasePresenter
{

	/** @var IFacebookFactory @inject */
	public $iFacebookFactory;

	/** @var ILinkedinFactory @inject */
	public $iLinkedinFactory;

	/** @var Candidate */
	private $candidate;

	/** @var Job */
	private $job;

	/** @var Match */
	private $match;

	public function actionApplyButtons($postId, $userId, $redirectUrl, $template)
	{
		$jobRepo = $this->em->getRepository(Job::getClassName());
		$this->job = $jobRepo->findOneByWordpressId($postId);

		if ($userId) {
			$userRepo = $this->em->getRepository(User::getClassName());
			$user = $userRepo->find($userId);
			if ($user && $user->person->candidate->id) {
				$this->user->login($user);
				$this->candidate = $user->person->candidate;
				if ($this->job) {
					$this->match = $this->candidate->findMatch($this->job);
				}
			}
		}
	}

	public function renderApplyButtons($postId, $userId, $redirectUrl, $template)
	{
		$bigTemplates = [
			'2-columns',
			'3-columns',
//			'classic',
			'fancy',
			'map-view',
		];
		$this->template->isBigTemplate = in_array($template, $bigTemplates);
		$this->template->wordpressId = $postId;
		$this->template->thisLink = $redirectUrl;
		$this->template->job = $this->job;
		$this->template->candidate = $this->candidate;
		$this->template->match = $this->match;
	}

}
