<?php

namespace Beast\Framework\Tokens;

class Generator
{
    public function create(int $bytes = 16): string
    {
        return bin2hax(random_bytes($bytes));
    }
}
