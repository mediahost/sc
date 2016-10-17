<?php

namespace App\Components\Grids\Job;

use App\Components\BaseControl;
use App\Extensions\Grido\BaseGrid;
use App\Model\Entity\Company;
use App\Model\Entity\Job;
use Grido\DataSources\Doctrine;

class JobsGrid extends BaseControl
{

	/** @var Company */
	private $company;


	protected function createComponentGrid()
	{
		$grid = new BaseGrid();
		$grid->setTranslator($this->translator);
		$grid->setTheme(BaseGrid::THEME_SUPR);

		$grid->model = $this->getModel();

		$grid->setDefaultSort([
			'id' => 'DESC',
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

		$grid->addColumnText('description', 'Description')
			->setSortable()
			->setFilterText()
			->setSuggestion();
		$grid->getColumn('description')
			->setTruncate(30);

		if (!$this->company) {
			$grid->addColumnText('company', 'Company')
				->setSortable()
				->setFilterText()
				->setSuggestion();
		}


		$grid->addActionHref('view', 'View')
			->setIcon('fa fa-eye');

		$grid->addActionHref('edit', 'Edit')
			->setIcon('fa fa-edit');

		$grid->addActionHref('delete', 'Delete')
			->setIcon('fa fa-trash-o')
			->setConfirm(function ($item) {
				$message = $this->translator->translate('Are you sure you want to delete \'%s\'?');
				return sprintf($message, (string)$item);
			})
			->getElementPrototype()->class[] = 'red';

		$grid->setActionWidth("20%");

		return $grid;
	}

	public function getModel() {
		$repo = $this->em->getRepository(Job::getClassName());
		$qb = $repo->createQueryBuilder('j')
			->select('j, c')
			->innerJoin('j.company', 'c');
		if ($this->company) {
			$qb->where('j.company = :company')
				->setParameter('company', $this->company);
		}
		$model = new Doctrine($qb, [
			'company' => 'c.name'
		]);
		return $model;
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
