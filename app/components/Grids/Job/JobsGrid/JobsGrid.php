<?php

namespace App\Components\Grids\Job;

use App\Components\BaseControl;
use App\Components\Grido\BaseGrid;
use App\Model\Entity\Company;
use App\Model\Entity\Job;
use Grido\DataSources\Doctrine;
use Grido\Grid;
use Nette\Utils\Strings;

class JobsGrid extends BaseControl
{

	/** @var Company */
	private $company;

	/** @return Grid */
	protected function createComponentGrid()
	{
		$grid = new BaseGrid();
		$grid->setTranslator($this->translator);
		$grid->setTheme(BaseGrid::THEME_METRONIC);

		$repo = $this->em->getRepository(Job::getClassName());
		$qb = $repo->createQueryBuilder('j')
				->select('j, c')
				->innerJoin('j.company', 'c');
		if ($this->company) {
			$qb->where('j.company = :company')
					->setParameter('company', $this->company);
		}
		$grid->model = new Doctrine($qb, [
			'company' => 'c.name'
		]);

		$grid->setDefaultSort([
			'id' => 'DESC',
			'name' => 'ASC',
		]);

		$grid->addColumnNumber('id', 'ID #')
				->setSortable()
				->setFilterNumber();
		$grid->getColumn('id')->headerPrototype->width = '5%';

		$grid->addColumnText('name', 'Name')
				->setSortable()
				->setFilterText()
				->setSuggestion();

		$grid->addColumnText('description', 'Description')
				->setSortable()
				->setFilterText()
				->setSuggestion();
		$grid->getColumn('description')
				->setCustomRender(function ($item) {
					return Strings::truncate($item->description, 30);
				});

		if (!$this->company) {
			$grid->addColumnText('company', 'Company')
					->setSortable()
					->setFilterText()
					->setSuggestion();
		}


		$grid->addActionHref('view', 'View')
						->setIcon('fa fa-eye')
				->elementPrototype->class[] = 'blue';

		$grid->addActionHref('edit', 'Edit')
						->setIcon('fa fa-edit')
				->elementPrototype->class[] = 'yellow';

		$grid->addActionHref('delete', 'Delete')
						->setIcon('fa fa-trash-o')
						->setConfirm(function($item) {
							$message = $this->translator->translate('Are you sure you want to delete \'%s\'?');
							return sprintf($message, (string) $item);
						})
				->elementPrototype->class[] = 'red';

		return $grid;
	}

	public function setCompany(Company $company)
	{
		$this->company = $company;
		return $this;
	}

}

interface IJobsGridFactory
{

	/** @return JobsGrid */
	function create();
}
