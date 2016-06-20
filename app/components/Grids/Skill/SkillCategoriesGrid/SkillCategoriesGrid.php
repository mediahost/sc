<?php

namespace App\Components\Grids\Skill;

use App\Components\BaseControl;
use App\Components\Grido\BaseGrid;
use App\Model\Entity\SkillCategory;
use Grido\DataSources\Doctrine;
use Grido\Grid;

class SkillCategoriesGrid extends BaseControl
{

	/** @return Grid */
	protected function createComponentGrid()
	{
		$grid = new BaseGrid();
		$grid->setTranslator($this->translator);
		$grid->setTheme(BaseGrid::THEME_METRONIC);

		$repo = $this->em->getRepository(SkillCategory::getClassName());
		$qb = $repo->createQueryBuilder('c')
				->select('c, p')
				->leftJoin('c.parent', 'p');
		$grid->model = new Doctrine($qb, [
			'parent' => 'p.name'
		]);

		$grid->setDefaultSort([
			'parent' => 'ASC',
			'name' => 'ASC',
			'id' => 'ASC',
		]);

		$grid->addColumnNumber('id', 'ID #')
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
						
		$grid->setActionWidth("15%");

		return $grid;
	}

}

interface ISkillCategoriesGridFactory
{

	/** @return SkillCategoriesGrid */
	function create();
}
