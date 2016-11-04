<?php

namespace App\Components\Grids\Job;

use App\Components\BaseControl;
use App\Extensions\Grido\BaseGrid;
use App\Model\Entity\Company;
use App\Model\Entity\Job;
use Grido\DataSources\Doctrine;
use Nette\Utils\Strings;

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
			->setCustomRender(function (Job $item) {
				return Strings::truncate($item->description, 30);
			})
			->setSortable()
			->setFilterText()
			->setSuggestion();

		$companyRepo = $this->em->getRepository(Company::getClassName());
		$companies = $companyRepo->findPairs('name');
		if (!$this->company) {
			$grid->addColumnText('company', 'Company')
				->setSortable()
				->setFilterSelect([NULL => '--- any ---'] + $companies);
		}

		$grid->addActionHref('view', 'View', 'Job:view')
			->setIcon('fa fa-eye');

		$grid->addActionHref('candidates', 'Candidates', 'Job:candidates')
			->setIcon('fa fa-users');

		$grid->addActionHref('edit', 'Edit', 'Job:edit')
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

	public function getModel()
	{
		$jobRepo = $this->em->getRepository(Job::getClassName());
		$qb = $jobRepo->createQueryBuilder('j')
			->select('j, c')
			->innerJoin('j.company', 'c');
		if ($this->company) {
			$qb->where('j.company = :company')
				->setParameter('company', $this->company);
		}
		$model = new Doctrine($qb, [
			'company' => 'c'
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
