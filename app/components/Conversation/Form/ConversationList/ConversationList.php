<?php

namespace App\Components\Conversation\Form;

use App\Components\BaseControl;
use App\Model\Entity\Communication;
use App\Model\Entity\Sender;

class ConversationList extends BaseControl
{

	const COMMUNICATIONS_PER_PAGE = 10;

	/** @var IFulltextSearchFactory @inject */
	public $iFulltextSearchFactory;

	/** @var Sender */
	private $sender;

	/** @var bool */
	private $allowSearchBox = TRUE;

	/** @var string */
	private $searchString;

	/** @var int @persistent */
	public $count = self::COMMUNICATIONS_PER_PAGE;

	/** @var Communication[] */
	protected $communications = [];

	/** @var Communication|NULL */
	protected $activeCommunication;

	public function render()
	{
		$this->template->addFilter('mark', $this->markSearchString);
		$this->template->sender = $this->sender;
		$this->template->communications = $this->communications;
		$this->template->activeCommunication = $this->activeCommunication;
		$this->template->communicationCount = $this->count;
		$this->template->communicationsPerPage = self::COMMUNICATIONS_PER_PAGE;
		$this->template->allowSearchBox = $this->allowSearchBox;
		parent::render();
	}

	/** @return FulltextSearch */
	public function createComponentSearchBox()
	{
		$control = $this->iFulltextSearchFactory->create();
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

	public function setSender(Sender $sender)
	{
		$this->sender = $sender;
		$this->activeCommunication = $this->sender->getLastCommunication();
		$this->communications = $this->sender->communications;
		return $this;
	}

	public function disableSearchBox($value = TRUE)
	{
		$this->allowSearchBox = !$value;
		return $this;
	}

}

interface IConversationListFactory
{

	/** @return ConversationList */
	public function create();

}