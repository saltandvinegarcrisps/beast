<?php

namespace Tests;

use Beast\Framework\Tokens\RedisStorage;
use PHPUnit\Framework\TestCase;

class RedisStorageTest extends TestCase
{
    public function testHasToken(): void
    {
        $redis = $this->createMock(\Redis::class);
        $redis->method('sIsMember')->willReturn(true);
        $storage = new RedisStorage($redis);
        $this->assertTrue($storage->has('test'));
    }

    public function testValidateToken(): void
    {
        $redis = $this->createMock(\Redis::class);
        $redis->method('sIsMember')->willReturn(true);
        $redis->method('sRemove')->willReturn(true);
        $storage = new RedisStorage($redis);
        $this->assertTrue($storage->validate('test'));
    }

    public function testValidateTokenFail(): void
    {
        $redis = $this->createMock(\Redis::class);
        $redis->method('sIsMember')->willReturn(false);
        $storage = new RedisStorage($redis);
        $this->assertFalse($storage->validate('test'));
    }

    public function testPutToken(): void
    {
        $redis = $this->createMock(\Redis::class);
        $redis->method('sAdd')->willReturn(1);
        $storage = new RedisStorage($redis);
        $this->assertTrue($storage->put('foo', 'bar'));
    }
}
