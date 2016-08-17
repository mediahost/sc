<?php

namespace App\Components\Auth;

use App\Components\BaseControl;
use App\Model\Entity;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;
use Fabian\Linkedin\Exception;
use Fabian\Linkedin\Linkedin;
use Fabian\Linkedin\LoginDialog;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

class LinkedinControl extends BaseControl
{

	/** @var array */
	public $onSuccess = [];

	/** @var array */
	public $onConnect = [];

	/** @var bool */
	private $onlyConnect = FALSE;

	/** @var Linkedin @inject */
	public $linkedin;

	/** @var SignUpStorage @inject */
	public $session;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	/**
	 * @var bool
	 * @persistent
	 */
	public $remember = FALSE;

	protected function createComponentDialog()
	{
		$dialog = $this->linkedin->createDialog();

		/** @var LoginDialog $dialog */
		$dialog->onResponse[] = function (LoginDialog $dialog) {
			$fields = [
				'id',
				'email-address',
				'first-name',
				'last-name',
				'maiden-name',
				'formatted-name',
				'headline',
				'location',
				'industry',
				'current-share',
				'summary',
				'specialties',
				'positions',
				'picture-url',
				'picture-urls',
				'site-standard-profile-request',
				'api-standard-profile-request',
				'public-profile-url',
			];

			try {
				$me = $this->linkedin->call(
					'people/~:(' . implode(',', $fields) . ')'
				);
				$me = ArrayHash::from($me);

				if ($this->onlyConnect) {
					$linkedinUser = new Entity\Linkedin($me->id);
					$this->loadLinkedinEntity($linkedinUser, $me);
					$this->onConnect($linkedinUser);
				} else {
					$user = $this->userFacade->findByLinkedinId($me->id);
					if ($user) {
						$this->loadLinkedinEntity($user->linkedin, $me);
						$this->em->getDao(Entity\Linkedin::getClassName())->save($user->linkedin);
					} else {
						$user = $this->createUser($me);
					}
					$this->onSuccess($this, $user, $this->remember);
				}
			} catch (Exception $e) {
				Debugger::log($e->getMessage(), 'linkedin');
				$this->presenter->flashMessage('We are sorry, LinkedIn authentication failed hard.');
			}
		};

		return $dialog;
	}

	public function render()
	{
		$template = $this->getTemplate();
		$template->link = $this->getLink();
		parent::render();
	}

	// <editor-fold desc="load & create">

	/**
	 * @param ArrayHash $me
	 * @return Entity\User
	 */
	protected function createUser(ArrayHash $me)
	{
		if (isset($me->emailAddress)) {
			$user = new Entity\User($me->emailAddress, TRUE);
		} else {
			$user = new Entity\User();
		}

		$linkedin = new Entity\Linkedin($me->id);
		$this->loadLinkedinEntity($linkedin, $me);

		$user->linkedin = $linkedin;
		return $user;
	}

	/**
	 * Load data to LinkedIn entity
	 * @param Entity\Linkedin $li
	 * @param ArrayHash $me
	 */
	protected function loadLinkedinEntity(Entity\Linkedin &$li, ArrayHash $me)
	{
		if (isset($me->emailAddress)) {
			$li->mail = $me->emailAddress;
		}
		if (isset($me->firstName)) {
			$li->firstname = $me->firstName;
		}
		if (isset($me->lastName)) {
			$li->surname = $me->lastName;
		}
		if (isset($me->formattedName)) {
			$li->name = $me->formattedName;
		}
		if (isset($me->location)) {
			if (isset($me->location->country)) {
				if (isset($me->location->country->code)) {
					$li->locationCode = $me->location->country->code;
				}
			}
			if (isset($me->location->name)) {
				$li->locationName = $me->location->name;
			}
		}
		if (isset($me->headline)) {
			$li->headline = $me->headline;
		}
		if (isset($me->industry)) {
			$li->industry = $me->industry;
		}
		if (isset($me->summary)) {
			$li->headline = $me->summary;
		}
		if (isset($me->pictureUrl)) {
			$li->pictureUrl = $me->pictureUrl;
		}
		if (isset($me->publicProfileUrl)) {
			$li->publicProfileUrl = $me->publicProfileUrl;
		}
		if (isset($me->siteStandardProfileRequest) && isset($me->siteStandardProfileRequest->url)) {
			$li->siteStandardProfileRequest = $me->siteStandardProfileRequest->url;
		}
	}

	// </editor-fold>
	// <editor-fold desc="setters">

	/**
	 * Fire onConnect event besides onSuccess
	 * @param bool $onlyConnect
	 * @return self
	 */
	public function setConnect($onlyConnect = TRUE)
	{
		$this->onlyConnect = $onlyConnect;
		return $this;
	}

	// </editor-fold>
	// <editor-fold desc="getters">

	/**
	 * return link to open dialog
	 * @return type
	 */
	public function getLink()
	{
		return $this->link('//dialog-open!');
	}

	// </editor-fold>
}

interface ILinkedinControlFactory
{

	/** @return LinkedinControl */
	function create();
}
