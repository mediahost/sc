<?php

namespace App\Components\AfterRegistration;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicHorizontalFormRenderer;
use App\Model\Entity\Address;
use App\Model\Entity\Candidate;
use App\Model\Entity\User;
use App\Model\Facade\UserFacade;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

class CompleteCandidateFirstControl extends BaseControl
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
		$this->setTemplateFile('candidateFirst');
		parent::render();
	}

	protected function createComponentForm()
	{
		/* @var $candidate Candidate */
		$candidate = $this->user->identity->candidate;

		$form = new Form();
		$form->setRenderer(new MetronicHorizontalFormRenderer());
		$form->setTranslator($this->translator);

		$form->addGroup('Personal Info');
		$form->addSelect('title', 'Title', Candidate::getTitleList())
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
		$form->addRadioList('gender', 'Gender', Candidate::getGenderList())
			->setDefaultValue('x')
			->setAttribute('class', 'custom-radio');

		$form->addDateInput('birthday', 'Birthday')
			->setDefaultValue($this->user->identity->socialBirthday)
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
		$form->addText('tel', 'Contact number')
			->setAttribute('class', 'input-xlarge')
			->setRequired();

		$form->addGroup('Photo');
		$form->addUpload('photo', 'Photo')
			->setRequired(!$candidate->photo);

		$defaultsArr = [
			'title' => $candidate->title,
			'degreeFront' => $candidate->degreeBefore,
			'firstName' => $candidate->firstname,
			'middleName' => $candidate->middlename,
			'surname' => $candidate->surname,
			'degreeAfter' => $candidate->degreeAfter,
			'gender' => $candidate->gender,
			'birthday' => $candidate->birthday,
			'nationality' => $candidate->nationality,
			'tel' => $candidate->phone,
		];
		if ($candidate->address) {
			$defaultsArr += [
				'house' => $candidate->address->house,
				'street' => $candidate->address->street,
				'zipcode' => $candidate->address->zipcode,
				'city' => $candidate->address->city,
				'country' => $candidate->address->country,
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
		$user = $this->user->identity;
		$user->candidate->title = $values->title;
		$user->candidate->degreeBefore = $values->degreeFront;
		$user->candidate->firstname = $values->firstName;
		$user->candidate->middlename = $values->middleName;
		$user->candidate->surname = $values->surname;
		$user->candidate->degreeAfter = $values->degreeAfter;
		$user->candidate->gender = $values->gender;
		$user->candidate->birthday = $values->birthday;
		$user->candidate->nationality = $values->nationality;
		$user->candidate->phone = $values->tel;
		$user->candidate->setPhoto($values->photo);

		if ($user->candidate->address) {
			$address = $user->candidate->address;
		} else {
			$address = new Address();
		}
		$address->house = $values->house;
		$address->street = $values->street;
		$address->zipcode = $values->zipcode;
		$address->city = $values->city;
		$address->country = $values->country;
		$user->candidate->address = $address;

		$user = $userRepo->save($user);

		$this->onSuccess($this, $user->candidate);
	}

}

interface ICompleteCandidateFirstControlFactory
{

	/** @return CompleteCandidateFirstControl */
	function create();
}
