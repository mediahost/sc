<?php

namespace App\Components\Grids\Skill;

use App\Components\BaseControl;
use App\Extensions\Grido\BaseGrid;
use App\Model\Entity\SkillCategory;
use Grido\DataSources\Doctrine;

class SkillCategoriesGrid extends BaseControl
{

	protected function createComponentGrid()
	{
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
		$grid->getColumn('id')->getHeaderPrototype()->width = '5%';

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
						
		$grid->setActionWidth('170px');

		return $grid;
	}

	private function getModel() {
		$repo = $this->em->getRepository(SkillCategory::getClassName());
		$qb = $repo->createQueryBuilder('c')
			->select('c, p')
			->leftJoin('c.parent', 'p');
		$model = new Doctrine($qb, [
			'parent' => 'p.name'
		]);
		return $model;
	}
}

interface ISkillCategoriesGridFactory
{

	/** @return SkillCategoriesGrid */
	function create();
}
