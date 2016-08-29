<?php

namespace App\Components\Job;

use App\Components\BaseControl;
use App\Forms\Form;
use Nette\Application\Responses\JsonResponse;
use Nette\Http\IResponse;
use Nette\Utils\ArrayHash;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Job;

class Questions extends BaseControl
{
	const SEPARATOR = '|';

	/** @var Job */
	private $job;

	/** @var array */
	public $onAfterSave = [];


	public function render()
	{
		$this->template->questions = explode(self::SEPARATOR, $this->job->questions);
		parent::render();
	}

	public function handleEdit()
	{
		$this->setTemplateFile('edit');
		$this->redrawControl('questions');
	}

	public function handlePreview()
	{
		$this->redrawControl('questions');
	}

	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());
		$form->getElementPrototype()->addClass('ajax sendOnChange');

		$form->addText('question1', 'Question 1');
		$form->addText('question2', 'Question 2');
		$form->addText('question3', 'Question 3');
		$form->addText('question4', 'Question 4');
		$form->addText('question5', 'Question 5');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->save();
		$this->presenter->payload->status = IResponse::S200_OK;
		$this->presenter->sendPayload();
	}

	protected function load(ArrayHash $values)
	{
		$questions = implode(self::SEPARATOR, (array)$values);
		$this->job->questions = $questions;
		return $this;
	}

	private function save()
	{
		$cvRepo = $this->em->getRepository(Job::getClassName());
		$cvRepo->save($this->job);
		return $this;
	}

	protected function getDefaults()
	{
		$questions = explode(self::SEPARATOR, $this->job->questions);
		$values = [
			'question1' => isset($questions[0]) ? $questions[0] : '',
			'question2' => isset($questions[1]) ? $questions[1] : '',
			'question3' => isset($questions[2]) ? $questions[2] : '',
			'question4' => isset($questions[3]) ? $questions[3] : '',
			'question5' => isset($questions[4]) ? $questions[4] : '',
		];
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->job) {
			throw new JobException('Use setJob(\App\Model\Entity\Job) before render');
		}
	}

	public function setJob(Job $job)
	{
		$this->job = $job;
		return $this;
	}
}


Interface IQuestionsFactory
{
	/** @return Questions */
	function create();
}