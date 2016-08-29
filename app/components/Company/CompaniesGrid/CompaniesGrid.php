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

		$repo = $this->em->getRepository(Company::getClassName());
		$qb = $repo->createQueryBuilder('c');
		$grid->model = new Doctrine($qb);

		$grid->setDefaultSort([
			'id' => 'DESC',
		]);

		$grid->addColumnText('companyId', 'ID')
			->setSortable()
			->setFilterText();
		$grid->getColumn('companyId')->headerPrototype->width = '10%';

		$grid->addColumnText('name', 'Name')
			->setSortable()
			->setFilterText()
			->setSuggestion();
		$grid->getColumn('name')
			->setCustomRender(__DIR__ . '/companyName.latte');

		$grid->addColumnText('users', 'Users')
			->setCustomRender(__DIR__ . '/users.latte');
		$grid->getColumn('users')->headerPrototype->width = '20%';


		$grid->addActionHref('view', 'Public Profile', ':Front:CompanyProfile:')
			->setIcon('fa fa-eye');

		$grid->addActionHref('jobs', 'Jobs', 'Jobs:')
			->setIcon('fa fa-briefcase');

		$grid->addActionHref('editImages', 'Images')
			->setIcon('fa fa-image');

		$grid->addActionHref('edit', 'Edit')
			->setIcon('fa fa-edit');

		$grid->addActionHref('delete', 'Delete')
			->setIcon('fa fa-trash-o')
			->setConfirm(function ($item) {
				$message = $this->translator->translate('Are you sure you want to delete \'%s\'?');
				return sprintf($message, (string)$item);
			})
			->getElementPrototype()->class[] = 'red';

		$grid->setActionWidth("25%");

		return $grid;
	}

}

interface ICompaniesGridFactory
{

	/** @return CompaniesGrid */
	function create();
}
