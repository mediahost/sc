<?php

namespace App\Components\Cv;

use App\Forms\Form;
use App\Model\Entity\Address;
use App\Model\Entity\Education;
use Nette\Utils\ArrayHash;

class Educations extends CvForm
{
	/** @var Education */
	private $education;

	public function render()
	{
		$this->template->cv = $this->cv;
		$this->template->education = $this->education;
		parent::render();
	}

	public function handleEdit($eduId)
	{
		$this->template->activeId = $eduId;
		$eduDao = $this->em->getDao(Education::getClassName());
		$edu = $eduDao->find($eduId);
		$this->setEducation($edu);
		$this->redrawControl();
	}

	public function handleDelete($eduId)
	{
		$eduDao = $this->em->getDao(Education::getClassName());
		$edu = $eduDao->find($eduId);
		$eduDao->delete($edu);
		$this->cv->deleteEducation($edu);
		$this->redrawControl();
		$this->onAfterSave();
	}

	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();
		$form = $this->createFormInstance();
		$form->addHidden('id', 0);
		$form->addText('institution', 'Institution')->setRequired('Must be filled');
		$form->addText('city', 'City');
		$form->addText('country', 'Country');
		$form->addDateRangePicker('season', 'Date from');
		$form->addText('title', 'Title of qualification awarded');
		$form->addTextArea('subjects', 'Principal subjects / occupational skills covered');
		$form->setDefaults($this->getDefaults());
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		if ($values['id'] != 0) {
			$education = $this->em->getDao(Education::getClassName())->find($values['id']);
			$this->education = $education;
		}
		$form->setValues([], true);
		$this->load($values);
		$this->save();
		$this->redrawControl();
		$this->onAfterSave();
	}

	private function load(ArrayHash $values)
	{
		if (!$this->education) {
			$this->education = new Education();
		}
		$this->education->institution = $values->institution;
		$this->education->title = $values->title;
		$this->education->dateStart = $values->season['start'];
		$this->education->dateEnd = $values->season['end'];
		$this->education->subjects = $values->subjects;
		$this->education->address = new Address();
		$this->education->address->city = $values->city;
		$this->education->address->country = $values->country;

		$this->cv->addEducation($this->education);
		return $this;
	}

	protected function getDefaults()
	{
		$values = [];
		if ($this->education) {
			$values = [
				'id' => $this->education->id,
				'institution' => $this->education->institution,
				'title' => $this->education->title,
				'season' => array('start' => $this->education->dateStart, 'end' => $this->education->dateEnd),
				'subjects' => $this->education->subjects,
				'city' => $this->education->address->city,
				'country' => $this->education->address->country,
			];
		}
		return $values;
	}
}

interface IEducationsFactory
{

	/** @return Educations */
	function create();
}
