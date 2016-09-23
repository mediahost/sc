<?php

namespace App\Components\Grids\User;

use App\Components\BaseControl;
use App\Extensions\Grido\BaseGrid;
use App\Helpers;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use Grido\DataSources\Doctrine;
use Nette\Security\User as Identity;

class UsersGrid extends BaseControl
{

	/** @var Identity */
	private $identity;

	protected function createComponentGrid()
	{
		$grid = new BaseGrid();
		$grid->setTranslator($this->translator);
		$grid->setTheme(BaseGrid::THEME_SUPR);

		$repo = $this->em->getRepository(User::getClassName());
		$qb = $repo->createQueryBuilder('u')
			->select('u, r')
			->leftJoin('u.roles', 'r');
		$grid->model = new Doctrine($qb, [
			'roles' => 'r.id'
		]);

		$grid->setDefaultSort([
			'id' => 'ASC',
			'mail' => 'ASC'
		]);

		$col = $grid->addColumnNumber('id', '#');
		$col->setSortable()->setFilterNumber();
		$col->getHeaderPrototype()->style['width'] = '5%';
		$col->cellPrototype->class[] = 'center';

		$col = $grid->addColumnEmail('mail', 'Mail');
		$col->setSortable()->setFilterText()->setSuggestion();
		$col->getHeaderPrototype()->style['width'] = '50%';

		$col = $grid->addColumnText('roles', 'Roles');
		$col->setSortable()
			->setFilterSelect($this->getRoles());
		$col->setCustomRender(__DIR__ . '/tag.latte')
			->setCustomRenderExport([$this, 'joinRoles']);
		$col->getHeaderPrototype()->style['width'] = '15%';

		$grid->addActionHref('access', 'Access')
			->setIcon('fa fa-key')
			->setDisable(function ($item) {
				return !$this->presenter->userFacade->canAccess($this->identity, $item);
			});

		$grid->addActionHref('edit', 'Edit')
			->setIcon('fa fa-edit')
			->setDisable(function ($item) {
				return !$this->presenter->userFacade->canEdit($this->identity, $item);
			});

		$grid->addActionHref('delete', 'Delete')
			->setIcon('fa fa-trash-o')
			->setConfirm(function ($item) {
				$message = $this->translator->translate('Are you sure you want to delete \'%s\'?');
				return sprintf($message, (string)$item);
			})
			->setDisable(function ($item) {
				return !$this->presenter->userFacade->canDelete($this->identity, $item);
			})
			->getElementPrototype()->class[] = 'red';

		$grid->setActionWidth("25%");

		$grid->setExport('users');

		return $grid;
	}

	public function setIdentity(Identity $identity)
	{
		$this->identity = $identity;
		return $this;
	}

	private function getRoles() {
		$roleRepo = $this->em->getRepository(Role::getClassName());
		return $roleRepo->findPairs('name');
	}

	private function joinRoles($item) {
		return Helpers::concatStrings(', ', $item->roles);
	}
}

interface IUsersGridFactory
{

	/** @return UsersGrid */
	function create();
}
