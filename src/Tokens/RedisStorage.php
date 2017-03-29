<?php

namespace Beast\Framework\Tokens;

class RedisStorage implements StorageInterface
{
    protected $channel;

    public function __construct($redis, string $channel = 'csrf_tokens')
    {
        $this->redis = $redis;
        $this->channel = $channel;
    }

    public function has(string $token): bool
    {
        return $this->redis->sIsMember($this->channel, $token);
    }

    public function validate(string $token): bool
    {
        if($this->redis->sIsMember($this->channel, $token)) {
            $this->redis->sRemove($this->channel, $token);
            return true;
        }
        return false;
    }

    public function put(string $token)
    {
        $this->redis->sAdd($this->channel, $token);
    }
}
