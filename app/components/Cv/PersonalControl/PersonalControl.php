<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Competences;
use App\Model\Entity\Cv;
use Nette\Utils\ArrayHash;

class PersonalControl extends BaseControl
{
	/** @var array */
	public $onAfterSave = [];

	/** @var Cv */
	private $cv;


	/**
	 * Creates component Form
	 * @return Form
	 */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->getElementPrototype()->addClass('ajax sendOnChange');
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());

		$form->addTextArea('social', 'Social skills and competences');
		$form->addTextArea('organisation', 'Organisational skills and competences');
		$form->addTextArea('technical', 'Technical skills and competences');
		$form->addTextArea('artictic', 'Artistic skills and competences');
		$form->addTextArea('other', 'Other skills and competences');
		$form->addMultiSelect2('licenses', 'Driving licenses', Competences::getDrivingLicensesList());

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
		$this->load($values);
		$this->save();
		$this->invalidateControl();
	}

	/**
	 * Fills Competences entity by form's values
	 * @param ArrayHash $values
	 * @return \App\Components\Cv\PersonalControl
	 */
	private function load(ArrayHash $values)
	{
		if (!$this->cv->competence) {
			$this->cv->competence = new Competences();
		}
		$this->cv->competence->social = $values->social;
		$this->cv->competence->organisation = $values->organisation;
		$this->cv->competence->technical = $values->technical;
		$this->cv->competence->artictic = $values->artictic;
		$this->cv->competence->other = $values->other;
		$this->cv->competence->drivingLicenses = $values->licenses;
		return $this;
	}

	/**
	 * Saves Cv entity
	 * @return \App\Components\Cv\PersonalControl
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
		if ($this->cv->competence) {
			$values = [
				'social' => $this->cv->competence->social,
				'organisation' => $this->cv->competence->organisation,
				'technical' => $this->cv->competence->technical,
				'artictic' => $this->cv->competence->artictic,
				'other' => $this->cv->competence->other,
				'licenses' => $this->cv->competence->drivingLicenses,
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
	 * @return \App\Components\Cv\WorksControl
	 */
	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}
}

/**
 * Definition IPersonalControlFactory
 * 
 */
interface IPersonalControlFactory
{

	/** @return PersonalControl */
	function create();
}
