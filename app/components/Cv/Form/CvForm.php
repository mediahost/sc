<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Cv;
use Nette\Utils\ArrayHash;

abstract class CvForm extends BaseControl
{
	/** @var array */
	public $onAfterSave = [];

	/** @var Cv */
	protected $cv;

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 * @return mixed
	 */
	abstract function formSucceeded(Form $form, ArrayHash $values);


	public function render() {
		if (!$this->isAjax || !$this->isSendOnChange) {
			$this['form']->addSubmit('save', 'Save');
		}
		parent::render();
	}

	protected function save()
	{
		$cvRepo = $this->em->getRepository(Cv::getClassName());
		$cvRepo->save($this->cv);
		return $this;
	}

	/** @return Form */
	protected function createFormInstance() {
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());
		if ($this->isAjax && $this->isSendOnChange) {
			$form->getElementPrototype()->class('ajax sendOnChange');
		} elseif ($this->isAjax) {
			$form->getElementPrototype()->class('ajax');
		}
		$form->onSuccess[] = $this->formSucceeded;
		$form->onError[] = $this->errorHandller;
		return $form;
	}

	public function errorHandller(Form $form) {
		foreach ($form->errors as $error) {
			$this->presenter->flashMessage($error, 'error');
		}
		$this->redrawControl();
		$this->presenter->redrawControl('flashMessages');
	}

	protected function checkEntityExistsBeforeRender() {
		if (!$this->cv) {
			throw new CvException('Use setCv(\App\Model\Entity\Cv) before render');
		}
	}

	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}
}