<?php

namespace App\Forms;

use Kdyby\Doctrine\EntityManager,
	Kdyby\Doctrine\EntityDao,
	App\Model\Entity\SkillCategory;

class SkillCategoryFormFactory extends FormFactory
{
	
	/** @var EntityDao */
	protected $skillCategoryDao;
	
	/** @var EntityManager */
	protected $em;
	
	public function __construct(IFormFactory $formFactory, EntityManager $em)
	{
		parent::__construct($formFactory);
		$this->em = $em;
		$this->skillCategoryDao = $this->em->getDao(SkillCategory::getClassName());
	}
	
	public function create()
	{
		$form = $this->formFactory->create();
		$form->addText('name', 'Name');
		$form->addSelect2('parent', 'Parent category', $this->skillCategoryDao->findPairs('name', 'id'))
			->setPrompt('---');
		
		$form->addSubmit('_submit', 'Save');
		$form->addSubmit('submitContinue', 'Save and continue edit');
		
		return $form;
	}
	
}
