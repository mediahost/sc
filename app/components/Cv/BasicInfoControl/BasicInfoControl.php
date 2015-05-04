<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Cv;
use Nette\Utils\ArrayHash;

/**
 * Cv data edit
 */
class BasicInfoControl extends BaseControl
{
	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var Cv */
	private $cv;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form;

		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer);

		$form->addText('name', 'Name');

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
		// TODO: no AJAX in form
		$this->redrawControl();
		$this->onAfterSave($this->cv);
	}

	private function load(ArrayHash $values)
	{
		$this->cv->name = $values->name;
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
			'name' => $this->cv->name,
		];
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->cv) {
			throw new CvControlException('Use setCv(\App\Model\Entity\Cv) before render');
		}
	}

	// <editor-fold desc="setters & getters">

	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}

	// </editor-fold>
}

interface IBasicInfoControlFactory
{

	/** @return BasicInfoControl */
	function create();
}
