<?php

namespace Beast\Framework\Tokens;

interface StorageInterface
{
	public function has(string $token): bool;

	public function put(string $token);
}
