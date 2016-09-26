<?php

namespace App\Extensions\Grido;

use App\Extensions\Grido\Columns\Boolean;
use Grido\Grid;

class BaseGrid extends Grid
{

	const THEME_METRONIC = 'metronic';
	const THEME_SUPR = 'supr';

	/** @var string */
	private $templateFile;

	/** @var string */
	private $actionWidth;

	/**
	 * Custom condition callback for filter birthday.
	 * @param string $value
	 * @return array|NULL
	 */
	public function birthdayFilterCondition($value)
	{
		$date = explode('.', $value);
		foreach ($date as &$val) {
			$val = (int)$val;
		}

		return count($date) == 3 ? ['birthday', '= ?', "{$date[2]}-{$date[1]}-{$date[0]}"] : NULL;
	}

	/**
	 * @param string $name
	 * @param string $label
	 * @return Boolean
	 */
	public function addColumnBoolean($name, $label)
	{
		$column = new Boolean($this, $name, $label);

		$header = $column->headerPrototype;
		$header->style['width'] = '2%';
		$header->class[] = 'center';

		return $column;
	}

	public function setTheme($theme = self::THEME_SUPR)
	{
		switch ($theme) {
			case self::THEME_SUPR:
			default:
				$this->templateFile = self::THEME_SUPR;
				$this->getTablePrototype()->class[] = 'table-bordered dataTable';
				break;
		}
	}

	public function setActionWidth($width)
	{
		$this->actionWidth = $width;
		return $this;
	}

	public function render()
	{
		if ($this->templateFile) {
			$this->setTemplateFile(__DIR__ . '/Themes/' . $this->templateFile . '.latte');
		}
		$this->template->actionWidth = $this->actionWidth;
		parent::render();
	}

}
