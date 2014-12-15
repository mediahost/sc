<?php

namespace App\Components\Company;

use App\Components\EntityControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Company;
use Kdyby\Doctrine\EntityManager;
use Nette\Utils\ArrayHash;

/**
 * Form with all company settings.
 * 
 * @method self setEntity(Company $entity)
 * @method Company getEntity()
 * @property Company $entity
 */
class CompanyControl extends EntityControl
{
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var EntityManager @inject */
	public $em;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer);

		$name = $form->addText('name', 'Name');

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$entity = $this->load($values);
		$companyDao = $this->em->getDao(Company::getClassName());
		$saved = $companyDao->save($entity);
		$this->onAfterSave($saved);
	}

	/**
	 * Load Entity from Form
	 * @param ArrayHash $values
	 * @return Company
	 */
	protected function load(ArrayHash $values)
	{
		$entity = $this->getEntity();
		$entity->name = $values->name;
		return $entity;
	}

	/**
	 * Get Entity for Form
	 * @return array
	 */
	protected function getDefaults()
	{
		$company = $this->getEntity();
		$values = [
			'name' => $company->name,
		];
		return $values;
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	protected function checkEntityType($entity)
	{
		return $entity instanceof Company;
	}

	/** @return Company */
	protected function getNewEntity()
	{
		return new Company;
	}

	// </editor-fold>
}

interface ICompanyControlFactory
{

	/** @return CompanyControl */
	function create();
}
