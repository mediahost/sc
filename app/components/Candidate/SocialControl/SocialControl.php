<?php

namespace App\Components\Candidate;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Candidate;
use Exception;
use Nette\Utils\ArrayHash;

/**
 * Description of SocialControl
 *
 */
class SocialControl extends BaseControl
{
	/** @var Candidate */
	public $candidate;

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];
	
	
	public function render()
	{
		$this->template->user = $this->candidate->user;
		$this->template->links = $this->getLinks();
		parent::render();
	}
	
	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());
			
		$form->addText('facebook', 'Facebook');
		$form->addText('twitter', 'Twitter');
		$form->addText('google', 'Google+:');
		$form->addText('linkedin', 'LinkedIn');
		$form->addText('pinterest', 'Pinterest');
		
		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}
	
	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->save();
		$this->onAfterSave($this->candidate);
	}
	
	protected function load(ArrayHash $values)
	{
		if($this->candidate->user->facebook) {
			$this->candidate->user->facebook->link = $values['facebook'];
		}
		if($this->candidate->user->twitter) {
			$this->candidate->user->twitter->url = $values['twitter'];
		}
	}
	
	protected function save()
	{
		$this->em->persist($this->candidate);
		$this->em->flush();
		return $this;
	}
	
	/** @return array */
	protected function getDefaults()
	{
		if($this->candidate->user->facebook) {
			$values['facebook'] = $this->candidate->user->facebook->link;
		}
		if($this->candidate->user->twitter) {
			$values['twitter'] = $this->candidate->user->twitter->url;
		}
		return $values;
	}
	
	private function checkEntityExistsBeforeRender()
	{
		if (!$this->candidate) {
			throw new ProfileControlException('Use setCandidate(\App\Model\Entity\Candidate) before render');
		}
	}
	
	private function getLinks() 
	{
		$links = [
			'facebook' => ['url' => 'facebook.com', 'title' => 'facebook.com'],
			'twitter' => ['url' => 'twitter.com', 'title' => 'twitter.com'],
			'google' => ['url' => 'plus.google.com', 'title' => 'plus.google.com'],
			'linkedin' => ['url' => 'linkd.in', 'title' => 'linkd.in'],
			'pinterst' => ['url' => 'pinterst.com', 'title' => 'pinterst.com']
		];
		
		if($this->candidate->user->facebook) {
			$links['facebook']['url'] = $this->candidate->user->facebook->link;
			$links['facebook']['title'] = $this->candidate->user->facebook;
		}
		if($this->candidate->user->twitter) {
			$links['twitter']['url'] = $this->candidate->user->twitter->url;
			$links['twitter']['title'] = $this->candidate->user->twitter;
		}
		return $links;
	}
	
	public function setCandidate(Candidate $candidate)
	{
		$this->candidate = $candidate;
		return $this;
	}
}


Interface ISocialControlFactory
{
	/** @return SocialControl */
	function create();
}