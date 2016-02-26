<?php

namespace App\Model\Entity\Traits;

use App\Model\Entity\Tag;
use App\Model\Entity\TagJob;
use Doctrine\Common\Collections\ArrayCollection;


trait JobTagsUsing
{
	/** @ORM\OneToMany(targetEntity="TagJob", mappedBy="job", cascade={"persist", "remove"}, orphanRemoval=true) */
	private $tags;
	
	/** @var ArrayCollection */
	private $newTags;
	
	
	public function setTag(TagJob $tag)
	{
		if(!$this->newTags) {
			$this->newTags = new ArrayCollection;
		}
		$this->newTags->add($tag);
		return $this;
	}
	
	public function getTags() 
	{
		return $this->tags;
	}
	
	public function removeTag(TagJob $tag) 
	{
		$this->tags->removeElement($tag);
		return $this;
	}
	
	public function removeOldTags() 
	{
		foreach($this->tags as $tagJob) {
			if (!$this->newTags->contains($tagJob)) {
				$this->removeTag($tagJob);
			}
		}
		foreach ($this->newTags as $tagJob) {
			if (!$this->tags->contains($tagJob)) {
				$this->tags->add($tagJob);
			}
		}
		return $this;
	}
}