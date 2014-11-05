<?php

namespace Test\Helpers;

use App\TaggedString;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * TEST: Tagged String
 *
 * @testCase
 * @phpVersion 5.4
 */
class TaggedStringTest extends TestCase
{

	public function testTaggedString()
	{
		$toReplace1 = 'test <%word1%> test <%word2%> test';
		$replacements1 =  ['word1' => 'test1', 'word2' => 'test2'];
		$taggedString1 = new TaggedString($toReplace1, $replacements1);
		Assert::same('test test1 test test2 test', (string) $taggedString1);
		
		$taggedString1->setReplacements([]);
		Assert::same($toReplace1, (string) $taggedString1);
		
		$taggedString1->setReplacements(['word1' => 'test1']);
		Assert::same('test test1 test <%word2%> test', (string) $taggedString1);
		
		$taggedString1->setReplacements(['word2' => 'test2']);
		Assert::same('test <%word1%> test test2 test', (string) $taggedString1);
		
		$toReplace2 = 'test word1 test word2 test';
		$taggedString1->setTaggedString($toReplace2);
		$taggedString1->setReplacements($replacements1);
		Assert::same($toReplace2, (string) $taggedString1);
		
	}

}

$test = new TaggedStringTest();
$test->run();
