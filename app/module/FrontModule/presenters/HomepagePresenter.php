<?php

namespace App\FrontModule\Presenters;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	public function actionDefault()
	{
		$entity = new \App\Model\Entity\Article($this->settingsStorage->languages->default);
		$entity->translate('fr')->title = 'fabuleux';
        $entity->translate('en')->title = 'awesome';
        $entity->translate('ru')->title = 'удивительный';
        $entity->translate('de')->title = 'fdfdf';
		$entity->mergeNewTranslations();
		
		$articleDao = $this->em->getDao(\App\Model\Entity\Article::getClassName());
		$articleDao->save($entity);
		
		$entityFinded = $articleDao->find($entity->id);
		\Tracy\Debugger::barDump($entityFinded->translate('fr')->title);
		\Tracy\Debugger::barDump($entityFinded->translate('en')->title);
		\Tracy\Debugger::barDump($entityFinded->title);
		
	}

	public function renderTest1()
	{
		$this->template->backlink = $this->storeRequest();
	}

	public function renderTest2()
	{
		$this->template->backlink = $this->storeRequest();
	}

}
