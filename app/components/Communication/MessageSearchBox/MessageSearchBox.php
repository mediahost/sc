<?php

namespace App\Components;

use App\Forms\Form;


class MessageSearchBox extends BaseControl {
    
    public $onSearch = [];
    
    /** @var \App\Model\Facade\CommunicationFacade */
    private $communicationFacade;
    
    /** @var \App\Model\Entity\User */
    private $user;
    
    
    public function __construct(\App\Model\Facade\CommunicationFacade $communicationFacade) {
        parent::__construct();
        $this->communicationFacade = $communicationFacade;
    }


    public function createComponentForm() {
        $form = new Form();
        $form->addText('searchString')->setAttribute('placeholder', 'Search for message ...');
        $form->onSuccess[] = $this->formSucceeded;
		return $form;
    }
    
    public function formSucceeded(Form $form, $values)
	{
        $communications = $this->communicationFacade->getUserCommunications($user, $search);
        $this->onSearch($communications);
    }
    
    public function setUser(\App\Model\Entity\User $user) {
        $this->user = $user;
    }
}

interface IMessageSearchBoxFactory
{

	/**
	 * @return MessageSearchBox
	 */
	public function create();

}