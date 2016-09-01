<?php

namespace App\Components\AfterRegistration;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicHorizontalFormRenderer;
use App\Model\Entity\Address;
use App\Model\Entity\Person;
use App\Model\Entity\User;
use App\Model\Facade\UserFacade;
use Nette\Utils\ArrayHash;

class CompletePerson extends BaseControl
{

	// <editor-fold desc="events">

	/** @var array */
	public $onSuccess = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var \Nette\Security\User @inject */
	public $user;

	// </editor-fold>

	public function render()
	{
		$this->setTemplateFile('person');
		parent::render();
	}

	protected function createComponentForm()
	{
		/* @var $person Person */
		$user = $this->user->getIdentity();
		$person = $user->getPerson();

		$form = new Form();
		$form->setRenderer(new MetronicHorizontalFormRenderer());
		$form->setTranslator($this->translator);

		$form->addGroup('Personal Info');
		$form->addSelect('title', 'Title', Person::getTitleList())
			->setAttribute('class', 'input-small');
		$form->addText('degreeFront', 'Degree in front of name')
			->setAttribute('class', 'input-large');
		$form->addText('firstName', 'First Name(s)')
			->setRequired();
		$form->addText('middleName', 'Middle Name');
		$form->addText('surname', 'Surname(s)')
			->setRequired();
		$form->addText('degreeAfter', 'Degree after name')
			->setAttribute('class', 'input-large');
		$form->addRadioList('gender', 'Gender', Person::getGenderList())
			->setDefaultValue('x')
			->setAttribute('class', 'custom-radio');

		$form->addDateInput('birthday', 'Birthday')
			->setDefaultValue($user->socialBirthday)
			->setRequired();

		$form->addSelect2('nationality', 'Nationality', Address::getCountriesList())
			->setAttribute('class', 'input-xlarge');

		$form->addGroup('Contact');

		$form->addText('house', 'House No.')
			->setAttribute('class', 'input-xlarge');
		$form->addText('street', 'Street address')
			->setAttribute('class', 'input-xlarge');
		$form->addText('zipcode', 'Postal code')
			->setAttribute('class', 'input-xlarge');
		$form->addText('city', 'City')
			->setAttribute('class', 'input-xlarge')
			->setRequired();
		$form->addSelect2('country', 'Country', Address::getCountriesList())
			->setAttribute('class', 'input-xlarge')
			->setRequired();
		$form->addText('phone', 'Contact number')
			->setAttribute('class', 'input-xlarge')
			->setRequired();

		$form->addGroup('Photo');
		$form->addUpload('photo', 'Photo')
			->setRequired(!$person->photo);

		$defaultsArr = [
			'title' => $person->title,
			'degreeFront' => $person->degreeBefore,
			'firstName' => $person->firstname,
			'middleName' => $person->middlename,
			'surname' => $person->surname,
			'degreeAfter' => $person->degreeAfter,
			'gender' => $person->gender,
			'birthday' => $person->birthday,
			'nationality' => $person->nationality,
			'phone' => $person->phone,
		];
		if ($person->address) {
			$address = $person->address;
			$defaultsArr += [
				'house' => $address->house,
				'street' => $address->street,
				'zipcode' => $address->zipcode,
				'city' => $address->city,
				'country' => $address->country,
			];
		}
		$form->setDefaults($defaultsArr);

		$form->addSubmit('save', 'Continue');

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$userRepo = $this->em->getRepository(User::getClassName());

		/* @var $user User */
		$user = $this->user->getIdentity();
		$person = $user->getPerson();

		$person->title = $values->title;
		$person->degreeBefore = $values->degreeFront;
		$person->firstname = $values->firstName;
		$person->middlename = $values->middleName;
		$person->surname = $values->surname;
		$person->degreeAfter = $values->degreeAfter;
		$person->gender = $values->gender;
		$person->birthday = $values->birthday;
		$person->nationality = $values->nationality;
		$person->phone = $values->phone;
		$person->setPhoto($values->photo);

		if ($person->address) {
			$address = $person->address;
		} else {
			$address = new Address();
		}
		$address->house = $values->house;
		$address->street = $values->street;
		$address->zipcode = $values->zipcode;
		$address->city = $values->city;
		$address->country = $values->country;
		$person->address = $address;

		$userRepo->save($user);

		$this->onSuccess($this, $person);
	}

}

interface ICompletePersonFactory
{

	/** @return CompletePerson */
	function create();
}
