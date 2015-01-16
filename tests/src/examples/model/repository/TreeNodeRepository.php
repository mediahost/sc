<?php

namespace Test\Examples\Model\Repository;

use Kdyby\Doctrine\EntityRepository;
use Knp\DoctrineBehaviors\ORM\Tree;

/**
 * https://github.com/KnpLabs/DoctrineBehaviors/blob/master/tests/fixtures/BehaviorFixtures/ORM/TreeNodeEntityRepository.php
 * https://github.com/KnpLabs/DoctrineBehaviors/blob/master/tests/fixtures/BehaviorFixtures/ORM/TreeNodeEntity.php
 */
class TreeNodeRepository extends EntityRepository
{

	use Tree\Tree;
}
