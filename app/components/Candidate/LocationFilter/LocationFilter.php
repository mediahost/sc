<?php

namespace App\Components\Candidate;

use App\Forms\Form;
use App\Forms\Renderers\MetronicHorizontalFormRenderer;
use App\Model\Entity\Candidate;
use App\Model\Entity\Person;
use Nette\Utils\ArrayHash;

class LocationFilter extends \App\Components\BaseControl 
{
    
    /** @var array */
	public $onAfterSend = [];
    
    /** @var array */
	private $locationRequests = [];
    
    
    public function render() {
        $jsonLocalities = [];
		foreach (Person::getLocalities() as $localityId => $locality) {
			$jsonLocalities[] = $this->loacationToLeaf($localityId, $locality);
		}
        $this->template->countries = Person::getLocalities(TRUE);
		$this->template->jsonCountries = $jsonLocalities;
        $this->setTemplateFile('default');
        parent::render();
    }
    
    public function renderPreview() {
        $this->template->locations = $this->locationRequests;
        $this->setTemplateFile('preview');
        parent::render();
    }
    
    protected function createComponentForm() {
        $form = new Form();
		$form->setRenderer(new MetronicHorizontalFormRenderer());
		$form->setTranslator($this->translator);
        $form->getElementPrototype()->class('ajax');
        
        $countryContainer = $form->addContainer('countries');
		foreach (Person::getLocalities(TRUE) as $countryId => $countryName) {
			$countryContainer->addCheckbox($countryId, $countryName)
				->setAttribute('class', 'inCountryTree');
		}
        
        $form->onSuccess[] = $this->formSucceeded;
		return $form;
    }
    
    public function formSucceeded(Form $form, ArrayHash $values)
	{
        $this->locationRequests = [];
        $countries = Candidate::getLocalities(TRUE);
		foreach ($values->countries as $countryId => $checked) {
			if ($checked) {
				$this->locationRequests[$countryId] = $countries[$countryId];
			}
		}
        $this->onAfterSend($this->locationRequests);
    }
    
    public function setLocationRequests($requests) {
        foreach ($requests as $id=>$request) {
            $this->locationRequests[$id] = $request;
        }
    }
    
    private function loacationToLeaf($id, $location)
	{
		$children = [];
		if (is_array($location)) {
			$leaf = [
				'text' => $id,
			];
			foreach ($location as $localityId => $localityName) {
				$children[] = $this->loacationToLeaf($localityId, $localityName);
			}
		} else {
			$leaf = [
				'id' => $id,
				'text' => $location,
			];
		}
		$leaf['state'] = [
			'selected' => false,
		];
		$leaf['children'] = $children;
		return $leaf;
	}
}

interface ILocationFilterFactory
{

	/** @return LocationFilter */
	function create();
}