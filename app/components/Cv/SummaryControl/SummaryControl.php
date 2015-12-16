<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Cv;
use Nette\Utils\ArrayHash;

class SummaryControl extends BaseControl
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

		$form->addCheckSwitch('show', 'Include in CV')
				->setOnText('Yes')
				->setOffText('No');
		$form->addTextArea('summary', 'Career summary')
				->getControlPrototype()
				->style = 'height: 200px;';

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
	 * @return \App\Components\Cv\SummaryControl
	 */
	private function load(ArrayHash $values)
	{
		$this->cv->careerSummary = $values->summary;
		$this->cv->careerSummaryIsPublic = $values->show;
		return $this;
	}

	/**
	 * Saves Cv entity
	 * @return \App\Components\Cv\SummaryControl
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
			'summary' => $this->cv->careerSummary,
			'show' => $this->cv->careerSummaryIsPublic,
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
	 * @return \App\Components\Cv\SummaryControl
	 */
	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}
}

/**
 * Definition ISummaryControlFactory
 * 
 */
interface ISummaryControlFactory
{

	/** @return SummaryControl */
	function create();
}
