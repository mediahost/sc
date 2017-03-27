<?php

namespace App\Components\Grids\Action;

use App\Components\BaseControl;
use App\Extensions\Grido\BaseGrid;
use App\Model\Entity\Action;
use App\Model\Entity\Job;
use App\Model\Entity\User;
use Grido\DataSources\Doctrine;
use Nette\Utils\Html;

class ActionsGrid extends BaseControl
{

	protected function createComponentGrid()
	{
		$grid = new BaseGrid();
		$grid->setTranslator($this->translator);
		$grid->setTheme(BaseGrid::THEME_METRONIC);

		$repo = $this->em->getRepository(Action::getClassName());
		$qb = $repo->createQueryBuilder('a')
			->select('a');
		$grid->model = new Doctrine($qb, []);

		$grid->setDefaultSort([
			'createdAt' => 'DESC',
		]);

		$users = $this->em->getRepository(User::getClassName())->findPairs('mail');
		$grid->addColumnText('user', 'User')
			->setSortable()
			->setFilterSelect([NULL => '---'] + $users);

		$jobs = $this->em->getRepository(Job::getClassName())->findPairs('name');
		$grid->addColumnText('job', 'Job')
			->setCustomRender(function (Action $item) {
				return Html::el('a class="btn btn-xs"')
					->setHref($this->presenter->link('Job:view', [
						'id' => $item->job->id,
					]))->setHtml($item->job);
			})
			->setSortable()
			->setFilterSelect([NULL => '---'] + $jobs);

		$types = Action::getTypes();
		$col = $grid->addColumnText('type', 'Type');
		$col->setFilterSelect([NULL => '---'] + $types);
		$col->setCustomRender(function (Action $item) {
			return $item->typeFormated;
		});
		$col->getHeaderPrototype()->width = '100px';

		$col = $grid->addColumnDate('createdAt', 'Time', 'd.m.Y H:i:s');
		$col->setSortable();
		$col->getHeaderPrototype()->width = '180px';

		$grid->addActionHref('delete', 'Delete')
			->setIcon('fa fa-trash-o')
			->setConfirm(function ($item) {
				$message = $this->translator->translate('Are you sure you want to delete \'%s\'?');
				return sprintf($message, (string)$item);
			})
			->getElementPrototype()->class[] = 'red';

		$grid->setActionWidth('170px');

		return $grid;
	}

}

interface IActionsGridFactory
{

	/** @return ActionsGrid */
	function create();
}
