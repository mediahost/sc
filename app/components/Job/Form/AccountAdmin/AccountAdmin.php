<?php

namespace App\Components\Job;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Job;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;

class AccountAdmin extends BaseControl
{
	/** @var array */
	public $onAfterSave = [];

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var Job */
	private $job;


	public function render()
	{
		$this->getAdmins();
		$this->template->job = $this->job;
		parent::render();
	}

	public function handleEdit()
	{
		$this->setTemplateFile('edit');
		$this->redrawControl('accountAdmin');
	}

	public function handlePreview()
	{
		$this->redrawControl('accountAdmin');
	}

	public function createComponentForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());
		$form->getElementPrototype()->addClass('ajax');

		$form->addSelect('admin', 'User', $this->getAdmins());
		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->save();
		$this->redrawControl('accountAdmin');
	}

	protected function getDefaults()
	{
		$result = [];
		if ($this->job->accountManager) {
			$result['admin'] = $this->job->accountManager->id;
		}
		return $result;
	}

	private function save()
	{
		$jobRepo = $this->em->getRepository(Job::getClassName());
		$jobRepo->save($this->job);
	}

	private function load($values)
	{
		$user = $this->em->getRepository(User::getClassName())->find($values->admin);
		$this->job->accountManager = $user;
		return $this;
	}

	private function getAdmins()
	{
		$role = $this->em->getRepository(Role::getClassName())->findOneByName(Role::ADMIN);
		$users = $this->em->getRepository(User::getClassName())->findPairsByRoleId($role->id, 'mail');
		return $users;
	}

	public function setJob(Job $job)
	{
		$this->job = $job;
		return $this;
	}
}

interface IAccountAdminFactory
{

	/** @return AccountAdmin */
	function create();
}
