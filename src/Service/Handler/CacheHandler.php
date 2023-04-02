<?php

namespace App\Service\Handler;

use Psr\Cache\InvalidArgumentException;
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
    const CACHE_BASE_CURRENCY_KEY_PREFIX = 'BASE_';
    const CACHE_RATES_CURRENCY_KEY_PREFIX = 'RATES_';
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

    /**
     * @param string $baseCurrency
     * @param array $exchangeRates
     * @return void
     */
    public function updateCurrencyCacheItems(string $baseCurrency, array $exchangeRates): void
    {
        $cacheCurrencies = $this->getItem(CacheHandler::CACHE_BASE_CURRENCY_KEY_PREFIX.$baseCurrency) ?: [];
        $cacheRates = $this->getItem(CacheHandler::CACHE_RATES_CURRENCY_KEY_PREFIX.$baseCurrency) ?: [];
        $this->setItem(CacheHandler::CACHE_BASE_CURRENCY_KEY_PREFIX.$baseCurrency, $cacheCurrencies + array_keys($exchangeRates));
        $this->setItem(CacheHandler::CACHE_RATES_CURRENCY_KEY_PREFIX.$baseCurrency, $cacheRates + $exchangeRates);
    }
}
