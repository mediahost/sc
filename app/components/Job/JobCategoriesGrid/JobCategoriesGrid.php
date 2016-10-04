<?php

namespace App\Components\Grids\Job;

use App\Components\BaseControl;
use App\Extensions\Grido\BaseGrid;
use App\Model\Entity\JobCategory;
use Grido\DataSources\Doctrine;

class JobCategoriesGrid extends BaseControl
{
	
	public function createComponentGrid() {
		$grid = new BaseGrid();
		$grid->setTranslator($this->translator);
		$grid->setTheme(BaseGrid::THEME_METRONIC);
		$grid->model = $this->getModel();

		$grid->setDefaultSort([
			'parent' => 'ASC',
			'name' => 'ASC',
			'id' => 'ASC',
		]);

		$grid->addColumnNumber('id', 'Id')
			->setSortable()
			->setFilterNumber();
		$grid->getColumn('id')->headerPrototype->width = '5%';

		$grid->addColumnText('name', 'Name')
			->setSortable()
			->setFilterText()
			->setSuggestion();

		$grid->addColumnText('parent', 'Parent Category')
			->setSortable()
			->setFilterText()
			->setSuggestion();


		$grid->addActionHref('edit', 'Edit')
			->setIcon('fa fa-edit');

		$grid->addActionHref('delete', 'Delete')
			->setIcon('fa fa-trash-o')
			->setConfirm(function($item) {
				$message = $this->translator->translate('Are you sure you want to delete \'%s\'?');
				return sprintf($message, (string) $item);
			})
			->getElementPrototype()->class[] = 'red';

		$grid->setActionWidth("20%");

		return $grid;
	}

	private function getModel() {
		$repo = $this->em->getRepository(JobCategory::getClassName());
		$qb = $repo->createQueryBuilder('c')
			->select('c, p')
			->leftJoin('c.parent', 'p');
		$model = new Doctrine($qb, [
			'parent' => 'p.name'
		]);
		return $model;
	}
}

Interface IJobCategoriesGridFactory
{
	/** @return JobCategoriesGrid */
	public function create();
}
