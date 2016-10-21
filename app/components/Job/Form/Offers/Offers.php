<?php

namespace App\Components\Job;

use App\Components\BaseControl;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Job;
use App\Model\Entity\TagJob;
use App\Model\Entity\Tag;

class Offers extends BaseControl
{
	/** @var Job */
	private $job;
	
	/** @var array */
	public $onAfterSave = [];
	
	
    public function render() {
	    $this->template->job = $this->job;
        $this->template->data = $this->getDefaults();
        parent::render();
    }
    
    public function handleEdit() {
        $this->setTemplateFile('edit');
        $this->redrawControl('offers');
    }
    
    public function handlePreview() {
        $this->redrawControl('offers');
    }

	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();
		
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());

		$form->addText('offers', 'Offers')
			->setAttribute('data-role', 'tagsinput')
			->setAttribute('placeholder', 'add a tag');
		$form->addText('requirements', 'Requirements')
			->setAttribute('data-role', 'tagsinput')
			->setAttribute('placeholder', 'add a tag');

        $form->addSubmit('save', 'Save');
		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->save();
		$this->onAfterSave($this->job);
	}

	protected function load(ArrayHash $values)
	{
		$tagRepo = $this->em->getRepository(Tag::getClassName());

		foreach (explode(',', $values['offers']) as $offer) {
			$tagJob = $this->createTagIfNotExits($offer, TagJob::TYPE_OFFERS);
			$this->job->tag = $tagJob;
		}
		foreach (explode(',', $values['requirements']) as $requirement) {
			$tagJob = $this->createTagIfNotExits($requirement, TagJob::TYPE_REQUIREMENTS);
			$this->job->tag = $tagJob;
		}
		$this->job->removeOldTags();
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
		return [
			'offers' => $this->getTags(TagJob::TYPE_OFFERS),
			'requirements' => $this->getTags(TagJob::TYPE_REQUIREMENTS)
		];
	}

	private function getTags($tagType)
	{
		$tags = [];
		foreach ($this->job->tags as $tagJob) {
			if ($tagJob->type == $tagType) {
				$tags[] = $tagJob->tag->name;
			}
		}
		return implode(',', $tags);
	}

	private function createTagIfNotExits($tagName, $tagType)
	{
		foreach ($this->job->tags as $tagJob) {
			if ($tagJob->tag == $tagName && $tagJob->type == $tagType) {
				return $tagJob;
			}
		}
		$tagRepo = $this->em->getRepository(Tag::getClassName());
		$newTag = new Tag($tagName);
		$tagRepo->save($newTag);
		$newTagJob = new TagJob();
		$newTagJob->job = $this->job;
		$newTagJob->tag = $newTag;
		$newTagJob->type = $tagType;
		return $newTagJob;
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

interface IOffersFactory
{

	/** @return Offers */
	function create();
}

