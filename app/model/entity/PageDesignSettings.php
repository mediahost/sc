<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * Page design settings (for Metronic)
 * @ORM\Entity
 */
class PageDesignSettings extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/** @ORM\Column(type="string", length=50, nullable=true) */
	protected $color;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $pageHeaderFixed;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $pageSidebarClosed;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $pageSidebarFixed;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $pageFooterFixed;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $pageSidebarReversed;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $pageFullWidth;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $pageContainerBgSolid;

	/** @var array */
	private $defaultValues = [
		'color' => 'default',
		'pageHeaderFixed' => TRUE,
		'pageSidebarClosed' => FALSE,
		'pageSidebarFixed' => FALSE,
		'pageFooterFixed' => FALSE,
		'pageSidebarReversed' => FALSE,
		'pageFullWidth' => FALSE,
		'pageContainerBgSolid' => TRUE,
	];

	/**
	 * Returns property default value
	 * @param  string  property name
	 * @return mixed   property value
	 */
	public function &__get($name)
	{
		if ($this->$name === NULL) {
			if (array_key_exists($name, $this->defaultValues)) {
				return $this->defaultValues[$name];
			}
		}
		return parent::__get($name);
	}

}
