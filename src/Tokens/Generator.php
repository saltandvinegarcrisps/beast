<?php

namespace Beast\Framework\Tokens;

class Generator
{
    /**
     * @param  int<1, max> $size
     * @return string
     */
    public function create(int $size = 32): string
    {
        $bytes = random_bytes($size);

        return bin2hex($bytes);
    }
}
