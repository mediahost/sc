<?php

namespace App\AppModule\Presenters;

class SkillCategoriesPresenter extends BasePresenter
{
	
	// <editor-fold defaultstate="collapsed" desc="actions & renderers">
	/**
	 * @secured
	 * @resource('admin')
	 * @privilege('add')
	 */
	public function actionAdd()
	{
		$this->setView("edit");
	}
	
	public function renderEdit()
	{
		$this->template->isAdd = TRUE;
	}
	
	// </editor-fold>
	
}
