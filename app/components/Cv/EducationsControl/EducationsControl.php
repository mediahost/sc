<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Address;
use App\Model\Entity\Cv;
use App\Model\Entity\Education;
use Nette\Utils\ArrayHash;

/**
 * EducationsControl class
 * 
 */
class EducationsControl extends BaseControl
{
	/** @var array */
	public $onAfterSave = [];

	/** @var Cv */
	private $cv;

	/** @var Education */
	private $education;

	
	/**
	 * Renders control
	 */
	public function render() {
		$this->template->cv = $this->cv;
		$this->template->education = $this->education;
		parent::render();
	}
	
	/**
	 * Edits Education entity
	 * @param int $eduId
	 */
	public function handleEdit($eduId) {
		$this->template->activeId = $eduId;
		$eduDao = $this->em->getDao(Education::getClassName());
		$edu = $eduDao->find($eduId);
		$this->setEducation($edu);
		$this->invalidateControl();
	}
	
	/**
	 * Deletes Education entity
	 * @param int $eduId
	 */
	public function handleDelete($eduId) {
		$eduDao = $this->em->getDao(Education::getClassName());
		$edu = $eduDao->find($eduId);
		$eduDao->delete($edu);
		$this->cv->deleteEducation($edu);
		$this->invalidateControl();
		$this->onAfterSave();
	}
	
	/**
	 * Creates component Form
	 * @return Form
	 */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->getElementPrototype()->addClass('ajax');
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());

		$form->addHidden('id', 0);
		$form->addText('institution', 'Institution')->setRequired('Must be filled');
		$form->addText('city', 'City');
		$form->addText('country', 'Country');
		$form->addDateRangePicker('season', 'Date from');
		$form->addText('title', 'Title of qualification awarded');
		$form->addTextArea('subjects', 'Principal subjects / occupational skills covered');

		$form->addSubmit('save', 'Save');
		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	/**
	 * Handler for onSuccess form's event
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function formSucceeded(Form $form, ArrayHash $values)
	{
		if($values['id'] != 0) {
			$edu = $this->em->getDao(Education::getClassName())->find($values['id']);
			$this->setEducation($edu);
		}
		$this->load($values);
		$this->save();
		$this->invalidateControl();
		$this->onAfterSave();
	}

	/**
	 * Fills Education entity by form's values
	 * @param ArrayHash $values
	 * @return \App\Components\Cv\EducationsControl
	 */
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

	/**
	 * Saves Cv entity
	 * @return \App\Components\Cv\EducationsControl
	 */
	private function save()
	{
		$cvRepo = $this->em->getRepository(Cv::getClassName());
		$cvRepo->save($this->cv);
		return $this;
	}

	/**
	 * Gets default values from entity
	 * @return array
	 */
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

	/**
	 * Checks if Cv entity exists
	 * @throws CvControlException
	 */
	private function checkEntityExistsBeforeRender()
	{
		if (!$this->cv) {
			throw new CvControlException('Use setCv(\App\Model\Entity\Cv) before render');
		}
	}

	/**
	 * Seter for Cv entity
	 * @param Cv $cv
	 * @return \App\Components\Cv\EducationsControl
	 */
	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}

	/**
	 * Seter for Education entity
	 * @param Education $education
	 * @return \App\Components\Cv\EducationsControl
	 */
	public function setEducation(Education $education)
	{
		$this->education = $education;
		return $this;
	}
}

/**
 * Definition IEducationsControlFactory
 * 
 */
interface IEducationsControlFactory
{

	/** @return EducationsControl */
	function create();
}
