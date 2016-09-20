<?php

namespace App\Components;

use App\Extensions\Grido\BaseGrid;
use App\Model\Entity\Communication;
use App\Model\Facade\CommunicationFacade;
use Grido\DataSources\Doctrine;


class ConversationsGrid extends BaseControl {
    
    /** @var Entity\Communication[] */
	protected $communications = [];
    
    /** @var CommunicationFacade @inject */
	public $communicationFacade;


    protected function createComponentGrid() {
	    $grid = new BaseGrid();
	    $grid->setTranslator($this->translator);
	    $grid->setTheme(BaseGrid::THEME_SUPR);
	    $grid->model = $this->getModel();

	    $grid->addColumnNumber('id', 'ID #')
		    ->setSortable()
		    ->setFilterNumber();

	    $grid->addColumnText('subject', 'Subject')
		    ->setSortable()
		    ->setFilterText();

	    $grid->addColumnText('lastMessage', 'Last message');

	    $grid->addColumnText('contributorsName', 'Contributors');

	    $grid->addActionHref('view', 'Messages', ':App:Messages:')
		    ->setIcon('fa fa-edit');

	    return $grid;
    }

    private function getModel() {
	    $repo = $this->em->getRepository(Communication::getClassName());
	    $builder = $repo->createQueryBuilder('c');
	    return new Doctrine($builder);
    }
}

interface IConversationsGridFactory
{

	/** @return ConversationsGrid */
	public function create();

}