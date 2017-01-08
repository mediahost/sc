<?php

namespace App\Components\Company;

use App\Components\BaseControl;
use App\Components\BaseControlException;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity;
use Nette\Utils\ArrayHash;

class CompanyAddress extends BaseControl
{

	/** @var array */
	public $onAfterSave = [];

	/** @var Entity\Address */
	private $address;

	/** @var bool */
	private $editable = FALSE;

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

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->save();
		$this->onAfterSave($this->address);
	}

	protected function load(ArrayHash $values)
	{
		$this->address->house = $values->house;
		$this->address->street = $values->street;
		$this->address->zipcode = $values->zipcode;
		$this->address->city = $values->city;
		$this->address->country = $values->country;
		return $this;
	}

	protected function save()
	{
		$this->em->persist($this->address);
		$this->em->flush();
		return $this;
	}

	protected function getDefaults()
	{
		$values = [
			'house' => $this->address->house,
			'street' => $this->address->street,
			'zipcode' => $this->address->zipcode,
			'city' => $this->address->city,
			'country' => ($this->address->getCountryName()) ? $this->address->country : NULL,
		];
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->address) {
			throw new BaseControlException('Use setAddress(\App\Model\Entity\Address) before render');
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	public function setAddress(Entity\Address $address)
	{
		$this->address = $address;
		return $this;
	}

	public function canEdit($value = TRUE)
	{
		$this->editable = $value;
		return $this;
	}

	// </editor-fold>
}

interface ICompanyAddressFactory
{

	/** @return CompanyAddress */
	function create();
}
