<?php

namespace App\Components\Candidate;

use App\Forms\Form;
use Nette\Utils\ArrayHash;


class SearchFilter extends \App\Components\BaseControl {
    
    /** @var array */
	public $onAfterSend = [];
    
    /** @var string */
    private $searchRequest;
    
    
    protected function createComponentForm() {
        $form = new Form();
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class('ajax sendOnChange');
        $form->addText('searchString')->setAttribute('placeholder', 'Search cv ...');
        $form->onSuccess[] = $this->formSucceeded;
		return $form;
    }
    
    public function formSucceeded(Form $form, ArrayHash $values) {
        $this->searchRequest = $values->searchString;
        $this->onAfterSend($this->searchRequest);
    }
}

interface ISearchFilterFactory
{

	/** @return SearchFilter */
	function create();
}