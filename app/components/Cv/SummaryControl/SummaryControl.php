<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Cv;
use Nette\Utils\ArrayHash;

class SummaryControl extends BaseControl
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

		$form->addCheckSwitch('show', 'Include to CV')
				->setOnText('Yes')
				->setOffText('No');
		$form->addTextArea('summary', 'Career summary')
				->getControlPrototype()
				->style = 'height: 200px;';

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
		$this->cv->careerSummary = $values->summary;
		$this->cv->careerSummaryIsPublic = $values->show;
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
		$values = [
			'summary' => $this->cv->careerSummary,
			'show' => $this->cv->careerSummaryIsPublic,
		];
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

interface ISummaryControlFactory
{

	/** @return SummaryControl */
	function create();
}
