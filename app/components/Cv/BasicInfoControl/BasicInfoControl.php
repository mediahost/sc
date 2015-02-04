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
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** 
	 * Private variable for Cv
	 * Use $this->cv or $this->getCv() instead
	 * @var Cv 
	 */
	private $_cv;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
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
		$this->loadData($values);
		$this->em->persist($this->cv);
		$this->em->flush();

		$this->redrawControl();
		$this->onAfterSave($this->cv);
	}

	/**
	 * Load data to CV
	 * @param ArrayHash $values
	 * @return Cv
	 */
	private function loadData(ArrayHash $values)
	{
		$this->cv->name = $values->name;
		return $this->cv;
	}

	/**
	 * Get Entity for Form
	 * @return array
	 */
	private function getDefaults()
	{
		$values = [
			'name' => $this->cv->name,
		];
		return $values;
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	/** @return self */
	public function setCv(Cv $cv)
	{
		$this->_cv = $cv;
		return $this;
	}

	/** @return Cv */
	private function &getCv()
	{
		if (!$this->_cv) {
			throw new CvControlException('Must use method setCv(\App\Model\Entity\Cv)');
		}
		return $this->_cv;
	}

	// </editor-fold>

	public function &__get($name)
	{
		if ($name === 'cv') {
			return $this->getCv();
		}
		return parent::__get($name);
	}

}

interface IBasicInfoControlFactory
{

	/** @return BasicInfoControl */
	function create();
}
