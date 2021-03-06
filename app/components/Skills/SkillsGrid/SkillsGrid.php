<?php

namespace App\Components\Grids\Skill;

use App\Components\BaseControl;
use App\Extensions\Grido\BaseGrid;
use App\Model\Entity\Skill;
use Grido\DataSources\Doctrine;

class SkillsGrid extends BaseControl
{

	protected function createComponentGrid()
	{
		$grid = new BaseGrid();
		$grid->setTranslator($this->translator);
		$grid->setTheme(BaseGrid::THEME_METRONIC);
		$grid->model = $this->getModel();

		$grid->setDefaultSort([
			'id' => 'ASC',
			'category' => 'ASC',
			'name' => 'ASC',
		]);

		$grid->addColumnNumber('id', 'Id')
				->setSortable()
				->setFilterNumber();
		$grid->getColumn('id')->headerPrototype->width = '5%';

		$grid->addColumnText('name', 'Name')
				->setSortable()
				->setFilterText()
				->setSuggestion();

		$grid->addColumnText('category', 'Category')
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
		$repo = $this->em->getRepository(Skill::getClassName());
		$qb = $repo->createQueryBuilder('s')
			->select('s, c')
			->innerJoin('s.category', 'c');
		$model = new Doctrine($qb, [
			'category' => 'c.name'
		]);
		return $model;
	}
}

interface ISkillsGridFactory
{

	/** @return SkillsGrid */
	function create();
}
