<?php

namespace Beast\Framework\Router\Middleware;

class MiddlewarePriority
{

	/**
	 * @var array<int, class-string>
	 */
	private array $priorityMiddleware = [];

	/**
	 * @param  array<int, class-string> $priorityMiddleware
	 * @return void
	 */
	public function __construct(array $priorityMiddleware)
	{
		$this->priorityMiddleware = $priorityMiddleware;
	}

	/**
	 * Return a priority-sorted list of middleware.
	 *
	 * This forces the listed middleware to always be in the given order.
	 * 
	 * @param  array<int, class-string> $middleware
	 * @return array<int, class-string>
	 */
	public function sort(array $middleware): array
	{
		$withinPriorityMap = array_filter($middleware, function (string $class): bool {
			return in_array($class, $this->priorityMiddleware, true);
		});

		$notWithinPriorityMap = array_filter($middleware, function (string $class): bool {
			return !in_array($class, $this->priorityMiddleware, true);
		});


		usort($withinPriorityMap, function ($a, $b) {
			return $this->getPriorityIndex($a) <=> $this->getPriorityIndex($b);
		});

		return array_merge($withinPriorityMap, $notWithinPriorityMap);
	}

	protected function getPriorityIndex(string $middleware): ?int
	{
		if (!in_array($middleware, $this->priorityMiddleware, true)) {
			return 0;
		}

		$priorityIndex = array_search($middleware, $this->priorityMiddleware);

		return $priorityIndex !== false ? $priorityIndex : 0;
	}
}
