<?php

namespace Test\Model\Entity;

use App\Model\Entity\PageDesignSettings;
use App\Model\Entity\User;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: PageDesignSettings entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class PageDesignSettingsTest extends TestCase
{

	public function testSetAndGet()
	{
		$values = [
			'color' => 'default',
			'pageHeaderFixed' => TRUE,
			'pageSidebarClosed' => FALSE,
			'pageSidebarFixed' => TRUE,
			'pageFooterFixed' => FALSE,
			'pageSidebarReversed' => TRUE,
			'pageFullWidth' => FALSE,
			'pageContainerBgSolid' => TRUE,
		];
		$user = new User;
		$user->mail = ('user@mail.com');

		$entity1 = new PageDesignSettings;
		Assert::null($entity1->id);
		Assert::count(0, $entity1->notNullValuesArray);
		Assert::count(10, $entity1->toArray());

		$entity1->user = $user;
		Assert::count(1, $entity1->notNullValuesArray);
		Assert::same($user->mail, $entity1->user->mail);

		$entity1->setValues($values);
		Assert::count(9, $entity1->notNullValuesArray);
		Assert::same($values['color'], $entity1->color);
		Assert::same($values['pageHeaderFixed'], $entity1->pageHeaderFixed);
		Assert::same($values['pageSidebarClosed'], $entity1->pageSidebarClosed);
		Assert::same($values['pageSidebarFixed'], $entity1->pageSidebarFixed);
		Assert::same($values['pageFooterFixed'], $entity1->pageFooterFixed);
		Assert::same($values['pageSidebarReversed'], $entity1->pageSidebarReversed);
		Assert::same($values['pageFullWidth'], $entity1->pageFullWidth);
		Assert::same($values['pageContainerBgSolid'], $entity1->pageContainerBgSolid);

		Assert::exception(function() use ($entity1) {
			$entity1->id = 123;
		}, 'Kdyby\Doctrine\MemberAccessException');

		// init only one value
		$entity2 = new PageDesignSettings;
		$entity2->color = $values['color'];
		Assert::same($values['color'], $entity2->color);
		Assert::count(1, $entity2->notNullValuesArray);

		// append
		$entity3 = new PageDesignSettings;
		Assert::count(0, $entity3->notNullValuesArray);
		$entity3->append($entity2);
		Assert::count(1, $entity3->notNullValuesArray);
		Assert::same($values['color'], $entity3->color);
		
		// append with rewrite
		$entity4 = new PageDesignSettings;
		$newColorValue = 'notDefault';
		$entity4->color = $newColorValue;
		Assert::same($newColorValue, $entity4->color);
		$entity4->append($entity2, TRUE);
		Assert::same($values['color'], $entity4->color);
		Assert::notSame($newColorValue, $entity4->color);
	}

}

$test = new PageDesignSettingsTest();
$test->run();
