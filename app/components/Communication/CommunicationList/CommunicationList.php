<?php

namespace App\Components;


use App\Model\Entity;
use Nette\Security\User;

class CommunicationList extends BaseControl
{

	/** @var Entity\Communication[] */
	protected $communications = [];

	/** @var array */
	protected $links = [];

	/** @var Entity\Communication|NULL */
	protected $activeCommunication;

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