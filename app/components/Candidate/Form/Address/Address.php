<?php

namespace App\Components\Candidate;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity;
use Nette\Utils\ArrayHash;

class Address extends BaseControl
{

	/** @var Entity\Candidate */
	public $candidate;

	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addText('house', 'House No.');
		$form->addText('street', 'Street address');
		$form->addText('zipcode', 'Postal code');
		$form->addText('city', 'City');
		$form->addSelect2('country', 'Country', Entity\Address::getCountriesList())
				->setPrompt('Not disclosed');
		$form->addSelect2('nationality', 'Nationality', Entity\Candidate::getNationalityList())
				->setPrompt('Not disclosed');
		$form->addText('phone', 'Contact number');

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->save();
		$this->onAfterSave($this->candidate);
	}

	protected function load(ArrayHash $values)
	{
		if (!$this->candidate->address) {
			$this->candidate->address = new Address();
		}
		$this->candidate->address->house = $values->house;
		$this->candidate->address->street = $values->street;
		$this->candidate->address->zipcode = $values->zipcode;
		$this->candidate->address->city = $values->city;
		$this->candidate->address->country = $values->country;
		$this->candidate->phone = $values->phone;
		$this->candidate->nationality = $values->nationality;
		return $this;
	}

	protected function save()
	{
		$this->em->persist($this->candidate);
		$this->em->flush();
		return $this;
	}

	/** @return array */
	protected function getDefaults()
	{
		$values = [
			'phone' => $this->candidate->phone,
			'nationality' => $this->candidate->nationality,
		];
		if ($this->candidate->address) {
			$values += [
				'house' => $this->candidate->address->house,
				'street' => $this->candidate->address->street,
				'zipcode' => $this->candidate->address->zipcode,
				'city' => $this->candidate->address->city,
				'country' => ($this->candidate->address->countryName) ? $this->candidate->address->country : NULL,
			];
		}
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->candidate) {
			throw new CandidateException('Use setCandidate(\App\Model\Entity\Candidate) before render');
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	public function setCandidate(Entity\Candidate $candidate)
	{
		$this->candidate = $candidate;
		return $this;
	}

	// </editor-fold>
}

interface IAddressFactory
{

	/** @return Address */
	function create();
}
