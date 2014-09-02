<?php

namespace Test\Helpers;

use Nette,
	Tester,
	Tester\Assert,
	App\Helpers;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * TEST: Helpers Testing
 *
 * @testCase
 * @phpVersion 5.4
 */
class HelpersTest extends Tester\TestCase
{

	public function setUp()
	{
		# Příprava
	}

	public function tearDown()
	{
		# Úklid
	}

	public function testInit()
	{
		Assert::exception(function() {
			$o = new Helpers;
		}, 'LogicException', 'Cannot instantiate static class App\Helpers');
	}

	public function testConcatStrings()
	{
		$concatStr1 = Helpers::concatStrings(', ', 'word1', 'word2', 'word3');
		Assert::same('word1, word2, word3', $concatStr1);
		$concatStr2 = Helpers::concatStrings(', ', ['word1', 'word2', 'word3']);
		Assert::same('word1, word2, word3', $concatStr2);
		$concatStr3 = Helpers::concatTwoStrings('word1', 'word2', ':');
		Assert::same('word1:word2', $concatStr3);
		$concatStr4 = Helpers::concatTwoStrings();
		Assert::same(NULL, $concatStr4);
		$concatStr5 = Helpers::concatStrings();
		Assert::same(NULL, $concatStr5);
		$concatStr6 = Helpers::concatStrings(NULL, ['word1', 'word2']);
		Assert::same('word1word2', $concatStr6);
	}

	public function testDateformatPHP2JS()
	{
		Assert::same('dd.mm.yyyy', Helpers::dateformatPHP2JS('d.m.Y'));
		Assert::same('dddd.mmmm.yyyy', Helpers::dateformatPHP2JS('dd.mm.yyyy'));
		Assert::same('', Helpers::dateformatPHP2JS(NULL));
		Assert::same('', Helpers::dateformatPHP2JS(FALSE));
	}

	public function testLinkToAnchor()
	{
		$exampleText1 = '... http://example.domain.com/test.php?foo=bar ...';
		$expectedText1 = '... <a href="http://example.domain.com/test.php?foo=bar" target="_blank">http://example.domain.com/test.php?foo=bar</a> ...';
		$expectedText2 = '... <a href="http://example.domain.com/test.php?foo=bar" class="test" target="_blank">http://example.domain.com/test.php?foo=bar</a> ...';
		$expectedText3 = '... <a href="http://example.domain.com/test.php?foo=bar">http://example.domain.com/test.php?foo=bar</a> ...';
		Assert::same($expectedText1, Helpers::linkToAnchor($exampleText1));
		Assert::same($expectedText2, Helpers::linkToAnchor($exampleText1, 'test'));
		Assert::same($expectedText3, Helpers::linkToAnchor($exampleText1, NULL, NULL));
	}

}

$test = new HelpersTest();
$test->run();
