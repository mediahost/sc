<?php

namespace App\Components\Skills;
use App\Components\BaseControl;

/**
 * Class SkillCategoryDataView
 * @package App\Components\Skills
 */
class SkillCategoryDataView extends BaseControl
{
    /** @var array */
    private $skillCategories = [];


    /**
     * @inheritdoc
     */
    public function render()
    {
        $this->template->skillCategories = $this->skillCategories;
        parent::render();
    }

    /**
     * Setter form SkillCategories
     * @param $skillCategories
     * @return $this
     */
    public function setSkillsCategories($skillCategories)
    {
        $this->skillCategories = $skillCategories;
        return $this;
    }
}


/**
 * Interface ISkillCategoryDataView
 * @package App\Components\Skills
 */
Interface ISkillCategoryDataViewFactory
{
    /** @return SkillCategoryDataView */
    public function create();
}