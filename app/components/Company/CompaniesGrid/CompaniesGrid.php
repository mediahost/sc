<?php

namespace App\Components\Grids\Company;

use App\Components\BaseControl;
use App\Extensions\Grido\BaseGrid;
use App\Model\Entity\Company;
use Grido\DataSources\Doctrine;

class CompaniesGrid extends BaseControl
{

	protected function createComponentGrid()
	{
		$grid = new BaseGrid();
		$grid->setTranslator($this->translator);
		$grid->setTheme(BaseGrid::THEME_METRONIC);
		$grid->model = $this->getModel();
		$grid->setDefaultSort([
			'id' => 'DESC',
		]);

		$col = $grid->addColumnText('companyId', 'Id');
		$col->setSortable()->setFilterText();
		$col->headerPrototype->width = '10%';

		$col = $grid->addColumnText('name', 'Name');
		$col->setSortable()->setFilterText()->setSuggestion();
		$col->setCustomRender(__DIR__ . '/companyName.latte');

		$col = $grid->addColumnText('address', 'Address');
		$col->setCustomRender([$this, 'formatAddress']);
		$col->setSortable()->setFilterText()->setSuggestion();

		$col = $grid->addColumnText('users', 'Users');
		$col->setCustomRender(__DIR__ . '/users.latte');
		$col->headerPrototype->width = '20%';

		$grid->addActionHref('jobs', 'Jobs', 'Jobs:company')
			->setIcon('fa fa-briefcase');

		$grid->addActionHref('edit', 'Edit')
			->setIcon('fa fa-edit');

		$grid->addActionHref('delete', 'Delete')
			->setIcon('fa fa-trash-o')
			->setConfirm([$this, 'getConfirmMessage'])
			->getElementPrototype()->class[] = 'red';

		$grid->setActionWidth('180px');
		return $grid;
	}

	public function formatAddress($row) {
		return (string) $row->address->format();
	}

	private function getModel() {
		$repo = $this->em->getRepository(Company::getClassName());
		$qb = $repo->createQueryBuilder('c')
			->select('c, a')
			->leftJoin('c.address', 'a');
		$model = new Doctrine($qb, ['address', 'a.street']);
		return $model;
	}

	public function getConfirmMessage($item)
	{
		$message = $this->translator->translate('Are you sure you want to delete \'%s\'?');
		return sprintf($message, (string)$item);
	}
}

interface ICompaniesGridFactory
{

	/** @return CompaniesGrid */
	function create();
}
