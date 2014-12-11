<?php

namespace Test\Examples\Model\Entity;

use App\Model\Entity\BaseTranslatable;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model;

/**
 * @ORM\Entity
 *
 * @property string $title
 */
class Article extends BaseTranslatable
{

	use Model\Translatable\Translatable;
}
