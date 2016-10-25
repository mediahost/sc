<?php

namespace App\Components\Candidate;

use App\Components\BaseControl;
use App\Components\BaseControlException;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity;
use Nette\Utils\ArrayHash;

class Address extends BaseControl
{

	/** @var Entity\Person */
	private $person;

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
		$form->addSelect2('nationality', 'Nationality', Entity\Person::getNationalityList())
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
		$this->onAfterSave($this->person);
	}

	protected function load(ArrayHash $values)
	{
		if (!$this->person->address) {
			$this->person->address = new Entity\Address();
		}
		$this->person->address->house = $values->house;
		$this->person->address->street = $values->street;
		$this->person->address->zipcode = $values->zipcode;
		$this->person->address->city = $values->city;
		$this->person->address->country = $values->country;
		$this->person->phone = $values->phone;
		$this->person->nationality = $values->nationality;
		return $this;
	}

	protected function save()
	{
		$this->em->persist($this->person);
		$this->em->flush();
		return $this;
	}

	/** @return array */
	protected function getDefaults()
	{
		$values = [
			'phone' => $this->person->phone,
			'nationality' => $this->person->nationality,
		];
		if ($this->person->address) {
			$values += [
				'house' => $this->person->address->house,
				'street' => $this->person->address->street,
				'zipcode' => $this->person->address->zipcode,
				'city' => $this->person->address->city,
				'country' => ($this->person->address->getCountryName()) ? $this->person->address->country : NULL,
			];
		}
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->person) {
			throw new BaseControlException('Use setPerson(\App\Model\Entity\Person) before render');
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	public function setPerson(Entity\Person $person)
	{
		$this->person = $person;
		return $this;
	}

	// </editor-fold>
}

interface IAddressFactory
{

	/** @return Address */
	function create();
}
