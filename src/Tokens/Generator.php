<?php

namespace Beast\Framework\Tokens;

class Generator
{
    /**
     * Create a token
     *
     * @param int
     * @return string
     */
    public function create(int $size = 32): string
    {
        $bytes = random_bytes($size);

        return bin2hex($bytes);
    }
}
