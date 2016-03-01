<?php

namespace App\Components;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;

/**
 * MapView component
 *
 */
class MapView extends BaseControl 
{
	/** @var Job */
	private $job;
	
	/** @var array */
	public $onAfterSave = [];
	
	
	/**
	 * Renders control
	 */
	public function render() {
		
		$this->template->render(__DIR__ . '/MapView.latte');
	}
	
	public function createComponentForm() {
		$this->checkEntityExistsBeforeRender();

		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer);
		
		$form->addText('googleSearch');
		$form->addHidden('placeId');
		$form->addHidden('placeName');
		$form->addHidden('placeType');
		$form->addHidden('placeIcon');
		$form->addHidden('lat');
		$form->addHidden('lng');
		$form->addHidden('placeLocation');
		$form->addHidden('placeViewPort');
		
		
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
	
	protected function load(\Nette\Utils\ArrayHash $values)
	{
		if(!$this->job->location) {
			$this->job->location = new \App\Model\Entity\Location();
		}
		$this->job->location->placeId = $values->placeId;
		$this->job->location->placeName = $values->placeName;
		$this->job->location->placeType = $values->placeType;
		$this->job->location->placeIcon = $values->placeIcon;
		$this->job->location->placeLocation = $values->placeLocation;
		//$this->job->location->placeViewPort = $values->placeViewPort;
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
		if(!$this->job->location) {
			return [];
		}
		$values = [
			'placeId' => $this->job->location->placeId,
			'placeName' => $this->job->location->placeName,
			'placeType' => $this->job->location->placeType,
			'placeIcon' => $this->job->location->placeIcon,
			'lat' => $this->job->location->lat,
			'lng' => $this->job->location->lng,
			'placeLocation' => $this->job->location->placeLocation,
			//'placeViewPort' => $this->location->placeViewPort
		];
		return $values;
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

//==============================================================================
/**
 * Description of IMapViewFactory
 *
 */
interface IMapViewFactory {
	
	/** @return \App\Components\MapView */
	public function create();
}
