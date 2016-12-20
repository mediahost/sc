<?php

namespace App\ApiModule\Presenters;

class WpSupportPresenter extends BasePresenter
{

	public function renderApplyButtons($postId)
	{
		$this->template->postId = $postId;
	}

}
