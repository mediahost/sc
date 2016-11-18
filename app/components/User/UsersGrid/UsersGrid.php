<?php

namespace App\Components\Grids\User;

use App\Components\BaseControl;
use App\Extensions\Grido\BaseGrid;
use App\Helpers;
use App\Model\Entity\Company;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Facade\UserFacade;
use Grido\DataSources\Doctrine;
use Nette\Security\User as Identity;

class UsersGrid extends BaseControl
{

	/** @var Identity @inject */
	public $identity;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var Company */
	private $company;

	/** @var bool */
	private $asCompany;

	protected function createComponentGrid()
	{
		$grid = new BaseGrid();
		$grid->setTranslator($this->translator);
		$grid->setTheme(BaseGrid::THEME_SUPR);
		$grid->model = $this->getModel();
		$grid->setDefaultSort([
			'id' => 'ASC',
			'mail' => 'ASC'
		]);

		$col = $grid->addColumnNumber('id', 'Id');
		$col->setSortable()->setFilterNumber();
		$col->getHeaderPrototype()->width = '5%';
		$col->cellPrototype->class[] = 'center';

		$col = $grid->addColumnEmail('mail', 'Mail');
		$col->setSortable()->setFilterText()->setSuggestion();

		$col = $grid->addColumnBoolean('verificated', 'Verified');
		$col->setSortable()->setFilterSelect([
			NULL => '--- all ---',
			TRUE => 'Yes',
			FALSE => 'No',
		]);
		$col->getHeaderPrototype()->width = '90px';

		if (!$this->asCompany) {
			$col = $grid->addColumnText('roles', 'Roles');
			$col->setSortable()->setFilterSelect($this->getRoles());
			$col->setCustomRender(__DIR__ . '/tag.latte')
				->setCustomRenderExport([$this, 'joinRoles']);
			$col->getHeaderPrototype()->width = '10%';
		}

		$grid->addActionHref('access', 'Access')->setIcon('fa fa-key')
			->setDisable([$this, 'checkAccess']);

		$grid->addActionHref('edit', 'Edit')->setIcon('fa fa-edit')
			->setDisable([$this, 'checkEdit']);

		$grid->addActionHref('delete', 'Delete')->setIcon('fa fa-trash-o')
			->setConfirm([$this, 'getConfirmMessage'])
			->setDisable([$this, 'checkDelete'])
			->getElementPrototype()->class[] = 'red';

		$grid->setActionWidth($this->asCompany ? '180px' : '280px');
		$grid->setExport('users');
		return $grid;
	}

	public function setCompany(Company $company, $asCompany = FALSE)
	{
		$this->company = $company;
		$this->asCompany = $asCompany;
		return $this;
	}

	private function getModel()
	{
		$repo = $this->em->getRepository(User::getClassName());
		$qb = $repo->createQueryBuilder('u')
			->select('u, r')
			->leftJoin('u.roles', 'r');
		if ($this->company) {
			$qb->leftJoin('u.allowedCompanies', 'ac');
			$qb->where('ac.company = :company');
			$qb->setParameter('company', $this->company);
		}

		$model = new Doctrine($qb, [
			'roles' => 'r.id'
		]);
		return $model;
	}

	private function getRoles()
	{
		$roleRepo = $this->em->getRepository(Role::getClassName());
		$roles = $roleRepo->findPairs('name');
		$roles = [NULL => '--- all ---'] + $roles;
		return $roles;
	}

	public function joinRoles($item)
	{
		return Helpers::concatStrings(', ', $item->roles);
	}

	public function checkAccess($item)
	{
		return !$this->userFacade->canAccess($this->identity, $item);
	}

	public function checkEdit($item)
	{
		return !$this->userFacade->canEdit($this->identity, $item);
	}

	public function checkDelete($item)
	{
		return !$this->userFacade->canDelete($this->identity, $item);
	}

	public function getConfirmMessage($item)
	{
		$message = $this->translator->translate('Are you sure you want to delete \'%s\'?');
		return sprintf($message, (string)$item);
	}
}

interface IUsersGridFactory
{

	/** @return UsersGrid */
	function create();
}
