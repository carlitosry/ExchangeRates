<?php

namespace App\Service\Handler;

use Symfony\Contracts\Cache\CacheInterface;

/**
 * class CacheHandler
 *
 * @package    CacheHandler
 * @copyright  2023 carlitosry <reyes.syscom@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CacheHandler
{
    const RATES_CACHE = 'rates_from_api_cache_key';
    private CacheInterface $cache;

    public function __construct(
        CacheInterface $cache,
    )
    {
        $this->cache = $cache;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int|null $expire
     * @return void
     */
    public function setItem(string $key, mixed $value, int $expire = null) : void
    {
        $cacheItem = $this->cache->getItem($key);
        $cacheItem->set($value);

        if ($expire) {
            $cacheItem->expiresAfter($expire);
        }

        $this->cache->save($cacheItem);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getItem(string $key): mixed
    {
        $cacheItem = $this->cache->getItem($key);

        if (!$cacheItem->isHit()) {
            return null;
        }

        return $cacheItem->get();
    }
}
