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

	/** @var array */
	public $onAfterSave = [];

	/** @var Person */
	public $person;

	/** @var bool */
	private $editable = FALSE;

	public function render()
	{
		$this->template->items = $this->getItems();
		$this->template->editable = $this->editable;
		parent::render();
	}

	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addText('behanceLink', 'Behance');
		$form->addText('dribbbleLink', 'Dribbble');
		$form->addText('githubLink', 'Github');
		$form->addText('linkedinLink', 'LinkedIn');
		$form->addText('stackOverflowLink', 'Stack Overflow');

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
		$this->person->behanceLink = $values['behanceLink'];
		$this->person->dribbbleLink = $values['dribbbleLink'];
		$this->person->githubLink = $values['githubLink'];
		$this->person->linkedinLink = $values['linkedinLink'];
		$this->person->stackOverflowLink = $values['stackOverflowLink'];
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
		$values['behanceLink'] = $this->person->behanceLink;
		$values['dribbbleLink'] = $this->person->dribbbleLink;
		$values['githubLink'] = $this->person->githubLink;
		$values['linkedinLink'] = $this->person->linkedinLink;
		$values['stackOverflowLink'] = $this->person->stackOverflowLink;
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->person) {
			throw new BaseControlException('Use setPerson(\App\Model\Entity\Person) before render');
		}
	}

	private function getItems()
	{
		$items = [
			'behanceLink' => [
				'icon' => 'fa fa-behance-square',
				'name' => 'Behance',
				'link' => $this->normalizeLink($this->person->behanceLink),
			],
			'dribbbleLink' => [
				'icon' => 'fa fa-dribbble',
				'name' => 'Dribble',
				'link' => $this->normalizeLink($this->person->dribbbleLink),
			],
			'githubLink' => [
				'icon' => 'fa fa-github',
				'name' => 'Github',
				'link' => $this->normalizeLink($this->person->githubLink),
			],
			'linkedinLink' => [
				'icon' => 'fa fa-linkedin',
				'name' => 'LinkedIn',
				'link' => $this->normalizeLink($this->person->linkedinLink),
			],
			'stackOverflowLink' => [
				'icon' => 'fa fa-stack-overflow',
				'name' => 'Stack Overflow',
				'link' => $this->normalizeLink($this->person->stackOverflowLink),
			],
		];
		return $items;
	}

	/**
	 * Add http protocol
	 * @param array $link
	 * @return array
	 */
	private function normalizeLink($link)
	{
		if (strlen($link) && strpos($link, 'http') === FALSE) {
			$link = 'http://' . $link;
		}
		return $link;
	}

	public function setPerson(Person $person)
	{
		$this->person = $person;
		return $this;
	}

	public function canEdit($value = TRUE)
	{
		$this->editable = $value;
		return $this;
	}

}


Interface ISocialFactory
{
	/** @return Social */
	function create();
}