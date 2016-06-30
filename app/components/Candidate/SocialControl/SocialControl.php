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
		$this->candidate->user->facebookLink = $values['facebook'];
		$this->candidate->user->twitterLink = $values['twitter'];
		$this->candidate->user->googleLink = $values['google'];
		$this->candidate->user->linkedinLink = $values['linkedin'];
		$this->candidate->user->pinterestLink = $values['pinterest'];
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
		$values = array();
		$values['facebook'] = $this->candidate->user->facebookLink;
		$values['twitter'] = $this->candidate->user->twitterLink;
		$values['google'] = $this->candidate->user->googleLink;
		$values['linkedin'] = $this->candidate->user->linkedinLink;
		$values['pinterest'] = $this->candidate->user->pinterestLink;
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
			'facebook' => $this->candidate->user->facebookLink,
			'twitter' => $this->candidate->user->twitterLink,
			'google' => $this->candidate->user->googleLink,
			'linkedin' => $this->candidate->user->linkedinLink,
			'pinterest' => $this->candidate->user->pinterestLink
		];
		$links = $this->normalizeLinks($links);
		return $links;
	}
	
	/**
	 * Add http protocol
	 * @param array $links
	 * @return array
	 */
	private function normalizeLinks($links) {
		foreach ($links as &$link) {
			if(strlen($link)  &&  strpos($link, 'http') === false) {
				$link = 'http://' . $link;
			}
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