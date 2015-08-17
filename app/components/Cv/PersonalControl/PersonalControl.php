<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Competences;
use App\Model\Entity\Cv;
use Nette\Utils\ArrayHash;

class PersonalControl extends BaseControl
{
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var Cv */
	private $cv;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();

		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addTextArea('social', 'Social skills and competences');
		$form->addTextArea('organisation', 'Organisational skills and competences');
		$form->addTextArea('technical', 'Technical skills and competences');
		$form->addTextArea('artictic', 'Artistic skills and competences');
		$form->addTextArea('other', 'Other skills and competences');
		$form->addMultiSelect2('licenses', 'Driving licenses', Competences::getDrivingLicensesList());

		if ($this->isAjax && $this->isSendOnChange) {
			$form->getElementPrototype()->class('ajax sendOnChange');
		} else {
			$form->addSubmit('save', 'Save');
		}

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

	// </editor-fold>
}

interface IPersonalControlFactory
{

	/** @return PersonalControl */
	function create();
}
