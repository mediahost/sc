<?php

namespace App\Components\Skills;

use App\Components\BaseControl;

/**
 * Class SkillDataView
 * @package App\Components\Skills
 */
class SkillDataView extends BaseControl
{
    /** @var array */
    private $skills = [];


    /**
     * @inheritdoc
     */
    public function render()
    {
        $this->template->skills = $this->skills;
        parent::render();
    }

    /**
     * Setter for skills
     * @param array $skills
     * @return SkillDataView $this
     */
    public function setSkills($skills)
    {
        $this->skills = $skills;
        return $this;
    }
}


/**
 * Interface ISkillsDataView
 * @package App\Components\Skills
 */
Interface ISkillDataViewFactory
{
    /** @return SkillDataView */
    public function create();
}