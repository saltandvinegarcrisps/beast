<?php

namespace Beast\Framework\Tokens;

class Generator
{
    public function create(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }
}
