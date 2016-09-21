<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Cv;

abstract class CvForm extends BaseControl
{
	/** @var array */
	public $onAfterSave = [];

	/** @var Cv */
	protected $cv;


	/** @return Form */
	protected function createFormInstance() {
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());
		if ($this->isAjax && $this->isSendOnChange) {
			$form->getElementPrototype()->class('ajax sendOnChange');
		} elseif ($this->isAjax) {
			$form->getElementPrototype()->class('ajax');
		} else {
			$form->addSubmit('save', 'Save');
		}
		return $form;
	}

	protected function save()
	{
		$cvRepo = $this->em->getRepository(Cv::getClassName());
		$cvRepo->save($this->cv);
		return $this;
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