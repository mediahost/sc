<?php

namespace App\Components;

use Nette\Utils\Html;
use App\Forms\Form;
use App\Model\Entity\Location;

class MapView extends BaseControl
{
	/** @var Location */
	private $location;

	public function __construct($caption = NULL)
	{
		parent::__construct($caption);
	}

	public function setValue($value)
	{
		if ($value == NULL) {
			$value = new Location;
		}
		$this->location = $value;
	}

	public function getValue()
	{
		return $this->location->placeId ? $this->location : null;
	}

	public function getControl()
	{
		$name = $this->getHtmlName();
		$block = Html::el('div');
		$block->add($this->getInput())
			->add($this->getMapDiv())
			->add(Html::el('input type="hidden"')->name($name . '[placeId]')->value($this->location->placeId))
			->add(Html::el('input type="hidden"')->name($name . '[placeName]')->value($this->location->placeName))
			->add(Html::el('input type="hidden"')->name($name . '[placeType]')->value($this->location->placeType))
			->add(Html::el('input type="hidden"')->name($name . '[placeIcon]')->value($this->location->placeIcon))
			->add(Html::el('input type="hidden"')->name($name . '[lat]')->value($this->location->lat))
			->add(Html::el('input type="hidden"')->name($name . '[lng]')->value($this->location->lng))
			->add(Html::el('input type="hidden"')->name($name . '[placeLocation]')->value($this->location->placeLocation))
			->add(Html::el('input type="hidden"')->name($name . '[placeViewport]')->value($this->location->placeViewport));
		return $block;
	}

	private function getInput()
	{
		$input = Html::el('input class="form-control googleSearch"')->name($this->getHtmlName() . '[googleSearch]')
			->value($this->location->placeName);
		return $input;
	}

	private function getMapDiv()
	{
		$div = Html::el('div')->id('mapView')->class('mapView');
		return $div;
	}

	public function loadHttpData()
	{
		$this->location->placeId = $this->getHttpData(Form::DATA_LINE, '[placeId]');
		$this->location->placeName = $this->getHttpData(Form::DATA_LINE, '[placeName]');
		$this->location->placeType = $this->getHttpData(Form::DATA_LINE, '[placeType]');
		$this->location->placeIcon = $this->getHttpData(Form::DATA_LINE, '[placeIcon]');
		$this->location->setPlaceLocation($this->getHttpData(Form::DATA_LINE, '[placeLocation]'));
		$this->location->placeViewport = $this->getHttpData(Form::DATA_LINE, '[placeViewport]');
	}
}
