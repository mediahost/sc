<?php

namespace App\Components;

use App\Model\Entity;

class CommunicationList extends BaseControl
{

	const COMMUNICATIONS_PER_PAGE = 10;

	/** @var IMessageSearchBoxFactory @inject */
	public $iMessageSearchBoxFactory;

	/** @var Sender */
	private $sender;

	/** @var Entity\Communication[] */
	protected $communications = [];

	/** @var Entity\Communication|NULL */
	protected $activeCommunication;

	/** @var array */
	protected $links = [];

	/** @var string */
	private $searchString;

	/** @var int @persistent */
	public $count = self::COMMUNICATIONS_PER_PAGE;

	public function render()
	{
		$this->template->addFilter('mark', $this->markSearchString);
		$this->template->sender = $this->sender;
		$this->template->communications = $this->communications;
		$this->template->activeCommunication = $this->activeCommunication;
		$this->template->communicationCount = $this->count;
		$this->template->communicationsPerPage = self::COMMUNICATIONS_PER_PAGE;
		parent::render();
	}

	public function createComponentMessageSearchBox()
	{
		$control = $this->iMessageSearchBoxFactory->create();
		$control->setSender($this->sender)
			->setAjax();
		$control->onSearch[] = function ($comunications) use ($control) {
			$this->searchString = $control->getSearched();
			$this->communications = $comunications;
			$this->presenter->redrawControl('messages');
			$this->presenter->redrawControl('messages-list');
		};
		return $control;
	}

	public function markSearchString($string)
	{
		$replacement = '<strong>' . $this->searchString . '</strong>';
		$string = str_replace($this->searchString, $replacement, $string);
		return $string;
	}

	public function setSender(Entity\Sender $sender)
	{
		$this->sender = $sender;
		$this->activeCommunication = $this->sender->getLastCommunication();
		$this->communications = $this->sender->communications;
		return $this;
	}
}

interface ICommunicationListFactory
{

	/** @return CommunicationList */
	public function create();

}