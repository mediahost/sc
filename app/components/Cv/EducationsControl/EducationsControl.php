<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Address;
use App\Model\Entity\Cv;
use App\Model\Entity\Education;
use Nette\Utils\ArrayHash;

class EducationsControl extends BaseControl
{
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var Cv */
	private $cv;

	/** @var Education */
	private $education;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();

		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addText('institution', 'Institution')
				->setRequired('Must be filled');
		$form->addText('city', 'City');
		$form->addText('country', 'Country');
		$form->addDatePicker('date_from', 'Date from');
		$form->addDatePicker('date_to', 'Date to');
		$form->addText('title', 'Title of qualification awarded');
		$form->addTextArea('subjects', 'Principal subjects / occupational skills covered');

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$this->load($values);
		$this->save();
		$this->onAfterSave($this->cv);
	}

	private function load(ArrayHash $values)
	{
		if (!$this->education) {
			$this->education = new Education();
		}
		$this->education->institution = $values->institution;
		$this->education->title = $values->title;
		$this->education->dateStart = $values->date_from;
		$this->education->dateEnd = $values->date_to;
		$this->education->subjects = $values->subjects;
		$this->education->address = new Address();
		$this->education->address->city = $values->city;
		$this->education->address->country = $values->country;
		
		$this->cv->addEducation($this->education);
		return $this;
	}

	private function save()
	{
		$cvRepo = $this->em->getRepository(Cv::getClassName());
		$cvRepo->save($this->cv);
		return $this;
	}

	/** @return array */
	protected function getDefaults()
	{
		$values = [];
		if ($this->education) {
			$values = [
				'institution' => $this->education->institution,
				'title' => $this->education->title,
				'date_from' => $this->education->dateStart,
				'date_to' => $this->education->dateEnd,
				'subjects' => $this->education->subjects,
				
				'city' => $this->education->address->city,
				'country' => $this->education->address->country,
			];
		}
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->cv) {
			throw new CvControlException('Use setCv(\App\Model\Entity\Cv) before render');
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}

	public function setEducation(Education $education)
	{
		$this->education = $education;
		return $this;
	}

	// </editor-fold>
}

interface IEducationsControlFactory
{

	/** @return EducationsControl */
	function create();
}
