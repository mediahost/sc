<?php

namespace App\Components\Conversation\Form;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Model\Entity\Sender;
use App\Model\Facade\CommunicationFacade;

class FulltextSearch extends BaseControl
{

	/** @var array */
	public $onSearch = [];

	/** @var CommunicationFacade @inject */
	public $communicationFacade;

	/** @var Sender */
	private $sender;

	/** @var string */
	private $search;

	public function createComponentForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		if ($this->isAjax) {
			$form->getElementPrototype()->class[] = 'ajax';
		}
		if ($this->isSendOnChange) {
			$form->getElementPrototype()->class[] = 'sendOnChange';
		}

		$form->addText('search')
			->setAttribute('placeholder', 'Search for message...');

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		if ($values->search) {
			$this->search = $values->search;
			$communications = $this->communicationFacade->findByFulltext($this->sender, $this->search);
		} else {
			$communications = $this->sender->communications;
		}
		$this->onSearch($communications);
	}

	public function getSearched()
	{
		return $this->search;
	}

	public function setSender(Sender $sender)
	{
		$this->sender = $sender;
		return $this;
	}

}

interface IFulltextSearchFactory
{

	/** @return FulltextSearch */
	public function create();

}