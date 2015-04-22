<?php

namespace App\Components\Grids\User;

use App\Components\BaseControl;
use App\Components\Grido\BaseGrid;
use App\Helpers;
use App\Model\Entity\User;
use Grido\DataSources\Doctrine;
use Grido\Grid;
use Nette\Security\User as Identity;

class UsersGrid extends BaseControl
{

	/** @var Identity */
	private $identity;

	/** @return Grid */
	protected function createComponentGrid()
	{
		$grid = new BaseGrid();
		$repo = $this->em->getRepository(User::class);
		$qb = $repo->createQueryBuilder('u');
		$model = new Doctrine($qb);
		$grid->model = $model;

		$grid->addColumnNumber('id', 'ID')
				->setSortable()
				->setFilterNumber();

		$grid->addColumnEmail('mail', 'Mail')
				->setSortable()
				->setFilterText();

		$grid->addColumnText('roles', 'Roles');
		$grid->getColumn('roles')
				->setCustomRender(function($item) {
					return Helpers::concatStrings(', ', $item->roles);
				});

		$grid->addActionHref('access', 'Access')
				->setIcon('icon-key')
				->setDisable(function($item) {
					return !$this->presenter->canAccess($this->identity, $item);
				});

		$grid->addActionHref('edit', 'Edit')
				->setIcon('icon-pencil')
				->setDisable(function($item) {
					return !$this->presenter->canEdit($this->identity, $item);
				});

		$grid->addActionHref('delete', 'Delete')
				->setIcon('icon-trash')
				->setConfirm(function($item) {
					$message = $this->translator->translate('Are you sure you want to delete \'%s\'?');
					return sprintf($message, (string) $item);
				})
				->setDisable(function($item) {
					return !$this->presenter->canDelete($this->identity, $item);
				});

		return $grid;
	}

	public function setIdentity(Identity $identity)
	{
		$this->identity = $identity;
		return $this;
	}

}

interface IUsersGridFactory
{

	/** @return UsersGrid */
	function create();
}
