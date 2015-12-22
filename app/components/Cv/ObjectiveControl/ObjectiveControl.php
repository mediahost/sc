<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Cv;
use Nette\Utils\ArrayHash;

class ObjectiveControl extends BaseControl
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

		$form->addCheckbox('show', 'Include in CV');
		$form->addTextArea('objective', 'Your career objective');

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
		$this->onAfterSave();
	}

	/**
	 * Fills Cv entity by form's values
	 * @param ArrayHash $values
	 * @return \App\Components\Cv\ObjectiveControl
	 */
	private function load(ArrayHash $values)
	{
		$this->cv->careerObjective = $values->objective;
		$this->cv->careerObjectiveIsPublic = $values->show;
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
		$values = [
			'objective' => $this->cv->careerObjective,
			'show' => $this->cv->careerObjectiveIsPublic,
		];
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
 * Definition IObjectiveControlFactory
 * 
 */
interface IObjectiveControlFactory
{

	/** @return ObjectiveControl */
	function create();
}
