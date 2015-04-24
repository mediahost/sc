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
		$grid->setTranslator($this->translator);
		$grid->setTheme(BaseGrid::THEME_METRONIC);
		$repo = $this->em->getRepository(User::class);
		$qb = $repo->createQueryBuilder('u');
		$model = new Doctrine($qb);
		$grid->model = $model;

		$grid->addColumnNumber('id', 'ID #')
				->setSortable()
				->setFilterNumber();
		$grid->getColumn('id')->headerPrototype->width = '5%';

		$grid->addColumnEmail('mail', 'Mail')
				->setSortable()
				->setFilterText()
				->setSuggestion();

		$grid->addColumnText('roles', 'Roles');
		$renderRoles = function ($item) {
			return Helpers::concatStrings(', ', $item->roles);
		};
		$grid->getColumn('roles')
				->setCustomRender($renderRoles)
				->setCustomRenderExport($renderRoles);

		$grid->addActionHref('access', 'Access')
						->setIcon('fa fa-key')
						->setDisable(function($item) {
							return !$this->presenter->canAccess($this->identity, $item);
						})->elementPrototype->class[] = 'btn-info';

		$grid->addActionHref('edit', 'Edit')
						->setIcon('fa fa-edit')
						->setDisable(function($item) {
							return !$this->presenter->canEdit($this->identity, $item);
						})->elementPrototype->class[] = 'yellow';

		$grid->addActionHref('delete', 'Delete')
						->setIcon('fa fa-trash-o')
						->setConfirm(function($item) {
							$message = $this->translator->translate('Are you sure you want to delete \'%s\'?');
							return sprintf($message, (string) $item);
						})
						->setDisable(function($item) {
							return !$this->presenter->canDelete($this->identity, $item);
						})->elementPrototype->class[] = 'red';

		$grid->setExport('users');

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
