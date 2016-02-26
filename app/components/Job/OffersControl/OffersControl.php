<?php

namespace App\Components\Job;

use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\TagJob;
use App\Model\Entity\Tag;

/**
 * Description of OffersControl
 *
 */
class OffersControl extends \App\Components\BaseControl
{
	/** @var Job */
	private $job;
	
	/** @var array */
	public $onAfterSave = [];
	
	
	/**
	 * @return \App\Forms\Form
	 */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();
		
		$form = new \App\Forms\Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer);
		
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
	
	public function formSucceeded(\App\Forms\Form $form, $values)
	{
		$this->load($values);
		$this->save();
		$this->onAfterSave($this->job);
	}
	
	protected function load(\Nette\Utils\ArrayHash $values)
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
		
		$cvRepo = $this->em->getRepository(\App\Model\Entity\Job::getClassName());
		$cvRepo->save($this->job);
		
		return $this;
	}
	
	/** @return array */
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
			if($tagJob->type == $tagType) {
				$tags[] = $tagJob->tag->name;
			}
		}
		return implode(',', $tags);
	}
	
	private function createTagIfNotExits($tagName, $tagType) 
	{
		foreach ($this->job->tags as $tagJob) {
			if($tagJob->tag == $tagName  &&  $tagJob->type == $tagType) {
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
			throw new JobControlException('Use setJob(\App\Model\Entity\Job) before render');
		}
	}
	
	public function setJob(\App\Model\Entity\Job $job)
	{
		$this->job = $job;
		return $this;
	}
}

interface IOffersControlFactory
{

	/** @return OffersControl */
	function create();
}

