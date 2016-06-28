<?php

namespace App\Components;

use App\Model\Entity;

class CommunicationList extends BaseControl
{

	const COMMUNICATIONS_PER_PAGE = 10;
    
    /** @var IMessageSearchBoxFactory */
	private $messageSearchBoxFactory;

	/** @var Entity\Communication[] */
	protected $communications = [];
    
    /** @var Entity\Communication[] */
    protected $searchedCommunications;

	/** @var array */
	protected $links = [];

	/** @var Entity\Communication|NULL */
	protected $activeCommunication;

	/** @var int @persistent */
	public $count = self::COMMUNICATIONS_PER_PAGE;
    
    
    public function __construct(IMessageSearchBoxFactory $messageSearchBoxFactory) {
        parent::__construct();
        $this->messageSearchBoxFactory = $messageSearchBoxFactory;
    }

	/**
	 * @param Entity\Communication $communication
	 * @param string|NULL $link
	 */
	public function addCommunication(Entity\Communication $communication, $link = NULL)
	{
		$this->communications[] = $communication;
		$this->links[$communication->id] = $link;
	}

	public function getCommunicationLink(Entity\Communication $communication)
	{
		$id = $communication->id;
		return isset($this->links[$id]) ? $this->links[$id] : NULL;
	}

	/**
	 * @param Entity\Communication|NULL $activeCommunication
	 */
	public function setActiveCommunication($activeCommunication)
	{
		$this->activeCommunication = $activeCommunication;
	}

	public function render()
	{
		$this->template->communications = $this->communications;
		$this->template->activeCommunication = $this->activeCommunication;
		$this->template->communicationCount = $this->count;
		$this->template->communicationsPerPage = self::COMMUNICATIONS_PER_PAGE;
		parent::render();
	}

    public function createComponentMessageSearchBox() {
        $control = $this->messageSearchBoxFactory->create();
        $control->setUser($this->template->user->identity);
        $control->onSearch[] = function($comunications) {
            $this->communications = $comunications;
            $this->presenter->redrawControl('messages');
            $this->presenter->redrawControl('messages-list');
        };
        return $control;
    }
}

interface ICommunicationListFactory
{

	/**
	 * @return CommunicationList
	 */
	public function create();

}