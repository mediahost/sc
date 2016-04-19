<?php

namespace App\Components\Cv;
use App\Components\BaseControl;

/**
 * Class CvDataView
 * @package App\Components\Cv
 */
class CvDataView extends BaseControl
{
    /** @var ArrayCollection */
    private $cvs;

    
    /**
     * @inheritdoc
     */
    public function render()
    {
        $this->template->cvs = $this->cvs;
        parent::render();
    }

    /**
     * @param $cvs
     * @return ArrayCollection
     */
    public function setCvs($cvs)
    {
        $this->cvs = $cvs;
        return $cvs;
    }
}

/**
 * Interface ICvDataViewFactory
 * @package App\Components\Cv
 */
Interface ICvDataViewFactory
{
    /** @return CvDataView */
    public function create();
}