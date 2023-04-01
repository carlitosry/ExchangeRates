<?php

namespace App\Tests;

use App\Service\Handler\CacheHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class CacheHandlerTest extends TestCase
{
    public function testSetItem()
    {
        // Create a mock CacheInterface
        $cache = $this->createMock(RedisAdapter::class);
        $cacheItem = $this->createMock(ItemInterface::class);


        // Set up expectations for the mock CacheInterface
        $cache->expects($this->atLeastOnce())
            ->method('getItem')
            ->with('foo')
            ->willReturn($cacheItem);

        $cache->expects($this->once())
            ->method('save')
            ->with($cacheItem);

        // Create a new instance of CacheHandler using the mock CacheInterface
        $cacheHandler = new CacheHandler($cache);

        // Call setItem on the CacheHandler
        $cacheHandler->setItem('foo', 'bar');

    }

    public function testGetItem()
    {
        // Create a mock CacheInterface
        $cache = $this->createMock(RedisAdapter::class);
        $cacheItem = $this->createMock(ItemInterface::class);

        $cacheItem->expects($this->once())
            ->method('get')
            ->willReturn('bar');

        $cacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(true);

        // Set up expectations for the mock CacheInterface
        $cache->expects($this->atLeastOnce())
            ->method('getItem')
            ->with('foo')
            ->willReturn($cacheItem);

        // Create a new instance of CacheHandler using the mock CacheInterface
        $cacheHandler = new CacheHandler($cache);

        // Call getItem on the CacheHandler to get the value we just set
        $result = $cacheHandler->getItem('foo');

        // Check that the result is equal to the value we set
        $this->assertEquals('bar', $result);

    }
}
