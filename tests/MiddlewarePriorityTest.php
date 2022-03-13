<?php

use Beast\Framework\Router\Middleware\MiddlewarePriority;
use PHPUnit\Framework\TestCase;

class MiddlewarePriorityTest extends TestCase
{
	/**
	 * @dataProvider dataProvider
	 */
	public function testEnsurePrioritySortingWorkingCorrectly(array $priority, array $expected, array $middlewares): void
	{
		$sorter = new MiddlewarePriority($priority);

		$this->assertEquals($expected, $sorter->sort($middlewares));
	}

	public function dataProvider(): array
	{
		return [
			'Orders correctly if all items are present in the data array' => [
				['cat', 'pig', 'dog'],
				['cat', 'pig', 'dog'],
				['dog', 'cat', 'pig'],
			],
			'Appends middleware to the end if missing from priority array' => [
				['cat', 'pig', 'dog'],
				['cat', 'pig', 'dog', 'elephant'],
				['dog', 'cat', 'elephant', 'pig'],
			],
		];
	}
}
