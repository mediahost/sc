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
		$form->addSelect('theme', 'Theme', [
			'default' => 'Default',
			'europass' => 'Euro Pass',
			'standard1' => 'Standard 1',
			'standard2' => 'Standard 2',
		]);

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
		if ($this->presenter->isAjax()) {
			$this->redrawControl();
		}
		$this->onAfterSave($this->cv);
	}

	private function load(ArrayHash $values)
	{
		$this->cv->name = $values->name;
		$this->cv->theme = $values->theme;
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
			'theme' => $this->cv->theme,
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

interface IBasicInfoControlFactory
{

	/** @return BasicInfoControl */
	function create();
}
