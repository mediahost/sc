<?php

namespace App\Components\Grids\Cv;

use App\Components\BaseControl;
use App\Components\Grido\BaseGrid;
use App\Model\Entity\Company;
use App\Model\Entity\Cv;
use Grido\DataSources\Doctrine;
use Grido\Grid;
use Nette\Utils\Strings;

class CvsGrid extends BaseControl
{

	/** @return Grid */
	protected function createComponentGrid()
	{
		$grid = new BaseGrid();
		$grid->setTranslator($this->translator);
		$grid->setTheme(BaseGrid::THEME_METRONIC);

		$repo = $this->em->getRepository(Cv::getClassName());
		$qb = $repo->createQueryBuilder('cv')
				->select('cv, cnd')
				->leftJoin('cv.candidate', 'cnd');
		$grid->model = new Doctrine($qb, [
			'candidate' => 'cnd.name',
		]);

		$grid->setDefaultSort([
			'id' => 'DESC',
		]);

		$grid->addColumnNumber('id', 'ID #')
				->setSortable()
				->setFilterNumber();
		$grid->getColumn('id')->headerPrototype->width = '5%';

		$grid->addColumnText('candidate', 'Candidate')
				->setSortable()
				->setFilterText()
				->setSuggestion();

		$grid->addColumnText('name', 'Name')
				->setSortable()
				->setFilterText()
				->setSuggestion();

		$grid->addColumnBoolean('isDefault', 'Default')
				->setSortable()
				->setFilterSelect([1 => 'Yes', 0 => 'No']);
        $grid->getColumn('isDefault')->cellPrototype->class[] = 'text-center';

		$grid->addActionHref('edit', 'Edit', 'CvEditor:')
						->setIcon('fa fa-edit');

		$grid->addActionHref('delete', 'Delete')
						->setIcon('fa fa-trash-o')
						->setConfirm(function($item) {
							$message = $this->translator->translate('Are you sure you want to delete \'%s\'?');
							return sprintf($message, (string) $item);
						})
				->elementPrototype->class[] = 'red';

		return $grid;
	}

}

interface ICvsGridFactory
{

	/** @return CvsGrid */
	function create();
}
