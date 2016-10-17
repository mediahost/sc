<?php

namespace App\Components\Candidate;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Candidate;
use App\Model\Entity\Person;
use Nette\Utils\ArrayHash;

class Social extends BaseControl
{
	/** @var Person */
	public $person;

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];


	public function render()
	{
		$this->template->links = $this->getLinks();
		parent::render();
	}

	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addText('facebook', 'Facebook');
		$form->addText('twitter', 'Twitter');
		$form->addText('google', 'Google+:');
		$form->addText('linkedin', 'LinkedIn');
		$form->addText('pinterest', 'Pinterest');

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
		$this->person->facebookLink = $values['facebook'];
		$this->person->twitterLink = $values['twitter'];
		$this->person->googleLink = $values['google'];
		$this->person->linkedinLink = $values['linkedin'];
		$this->person->pinterestLink = $values['pinterest'];
	}

	protected function save()
	{
		$this->em->persist($this->person);
		$this->em->flush();
		return $this;
	}

	protected function getDefaults()
	{
		$values = [];
		$values['facebook'] = $this->person->facebookLink;
		$values['twitter'] = $this->person->twitterLink;
		$values['google'] = $this->person->googleLink;
		$values['linkedin'] = $this->person->linkedinLink;
		$values['pinterest'] = $this->person->pinterestLink;
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->person) {
			throw new BaseControlException('Use setPerson(\App\Model\Entity\Person) before render');
		}
	}

	private function getLinks()
	{
		$links = [
			'facebook' => $this->person->facebookLink,
			'twitter' => $this->person->twitterLink,
			'google' => $this->person->googleLink,
			'linkedin' => $this->person->linkedinLink,
			'pinterest' => $this->person->pinterestLink
		];
		$links = $this->normalizeLinks($links);
		return $links;
	}

	/**
	 * Add http protocol
	 * @param array $links
	 * @return array
	 */
	private function normalizeLinks($links)
	{
		foreach ($links as &$link) {
			if (strlen($link) && strpos($link, 'http') === false) {
				$link = 'http://' . $link;
			}
		}
		return $links;
	}

	public function setPerson(Person $person)
	{
		$this->person = $person;
		return $this;
	}
}


Interface ISocialFactory
{
	/** @return Social */
	function create();
}