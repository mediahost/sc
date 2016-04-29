<?php

namespace App\Components\User;
use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;

/**
 * Class CareerDocs
 * @package App\Components\User
 */
class CareerDocsControl extends BaseControl
{
    /** @var array */
    public $onAfterSave = [];


    public function handleSend() {
        
    }
}


Interface ICareerDocsControlFactory
{
    /** @return CareerDocsControl */
    public function create();
}