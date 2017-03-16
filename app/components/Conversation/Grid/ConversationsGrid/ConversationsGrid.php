<?php

namespace App\Components;

use App\Extensions\Grido\BaseGrid;
use App\Model\Entity\Communication;
use App\Model\Entity\Job;
use App\Model\Facade\CommunicationFacade;
use Grido\DataSources\Doctrine;

class ConversationsGrid extends BaseControl
{

	/** @var array */
	protected $communications = [];

	/** @var CommunicationFacade @inject */
	public $communicationFacade;

	protected function createComponentGrid()
	{
		$grid = new BaseGrid();
		$grid->setTranslator($this->translator);
		$grid->setTheme(BaseGrid::THEME_SUPR);

		$repo = $this->em->getRepository(Communication::getClassName());
		$builder = $repo->createQueryBuilder('c')
			->select('c, s')
			->leftJoin('c.contributors', 's');
		$grid->model = new Doctrine($builder, [
			'contributors' => 's.id'
		]);

		$col = $grid->addColumnText('subject', 'Subject');
		$col->setSortable();
		$col->setFilterText();
		$col->getHeaderPrototype()->width = '280px';

		$jobs = $this->em->getRepository(Job::getClassName())->findPairs('name');
		$col = $grid->addColumnText('job', 'Job');
		$col->setSortable();
		$col->setFilterSelect([NULL => '---'] + $jobs);
		$col->getHeaderPrototype()->width = '280px';

		$senders = $this->communicationFacade->getSendersPairs();
		$col = $grid->addColumnText('contributors', 'Contributors');
		$col->setCustomRender(function (Communication $communication) {
			return $communication->getContributorsName();
		});
		$col->setFilterSelect([NULL => '---'] + $senders);
		$col->getHeaderPrototype()->width = '280px';

		$grid->addColumnText('lastMessage', 'Last message')
			->setTruncate(100);

		$grid->addActionHref('comunication', 'Messages', ':App:Messages:browse')
			->setIcon('fa fa-eye');

		$grid->setActionWidth('170px');
		$grid->setDefaultSort([
			'id' => 'DESC',
		]);

		return $grid;
	}

}

interface IConversationsGridFactory
{

	/** @return ConversationsGrid */
	public function create();

}