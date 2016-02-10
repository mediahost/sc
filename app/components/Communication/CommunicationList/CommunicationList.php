<?php

namespace App\Components;

use App\Model\Entity;

class CommunicationList extends BaseControl
{

	const COMMUNICATIONS_PER_PAGE = 2;

	/** @var Entity\Communication[] */
	protected $communications = [];

	/** @var array */
	protected $links = [];

	/** @var Entity\Communication|NULL */
	protected $activeCommunication;

	/** @var int @persistent */
	public $count = self::COMMUNICATIONS_PER_PAGE;

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

}

interface ICommunicationListFactory
{

	/**
	 * @return CommunicationList
	 */
	public function create();

}