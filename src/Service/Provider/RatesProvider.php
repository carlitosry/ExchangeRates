<?php

namespace App\Service\Provider;

use App\Model\RatesResponse;
use App\Repository\ExchangeRateRepository;
use App\Service\Handler\CacheHandler;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 *
 * @package
 * @copyright  2023 Carlos Reyes <reyes.syscom@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class RatesProvider
{
    public const BASE_CURRENCY = "EUR";
    private Client $client;
    private SerializerInterface $serializer;
    private CacheHandler $cache;
    private ExchangeRateRepository $exchangeRateRepository;

    public function __construct(
        Client $client,
        CacheHandler $cache,
        ExchangeRateRepository $exchangeRateRepository
    )
    {
        $this->client = $client;
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
        $this->cache = $cache;
        $this->exchangeRateRepository = $exchangeRateRepository;
    }

    /**
     * @throws GuzzleException|Exception
     */
    public function getRates(string $baseCurrency, array $targetCurrencies) : RatesResponse
    {
        $response = $this->client->request('GET', '', [
            'query' => [
                'base' => $baseCurrency,
                'symbols' => implode(',', $targetCurrencies)
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new Exception('The response status code is not valid');
        }

        return $this->serializer->deserialize($response->getBody()->getContents(), RatesResponse::class, 'json');
    }

    public function fetchRatesFromDatabase(string $baseCurrency, array $targetCurrencies) : array
    {
        $currenciesToCalculate = array_merge([$baseCurrency], $targetCurrencies);
        $currenciesInCache = $this->cache->getItem(CacheHandler::CACHE_BASE_CURRENCY_KEY_PREFIX.$baseCurrency) ?: [];

        if(!empty($currenciesInCache) && !empty($targetCurrencies) && count(array_diff($targetCurrencies, $currenciesInCache)) == 0){
            $ratesInCache = $this->cache->getItem(CacheHandler::CACHE_RATES_CURRENCY_KEY_PREFIX.$baseCurrency);
            return array_intersect_key($ratesInCache, array_flip($currenciesToCalculate));
        }

        $rates = $this->exchangeRateRepository->findRatesByBaseAndTargetCurrencies(self::BASE_CURRENCY,$currenciesToCalculate);

        $resultArray = array();
        foreach ($rates as $rate) {
            $resultArray[$rate['targetCurrency']] = $rate['rate'];
        }

        $this->cache->updateCurrencyCacheItems($baseCurrency, $resultArray);

        return $resultArray;
    }
}
