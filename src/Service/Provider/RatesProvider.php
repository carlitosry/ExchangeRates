<?php

namespace App\Service\Provider;

use App\Model\RatesResponse;
use App\Repository\ExchangeRateRepository;
use App\Service\Handler\CacheHandler;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
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
        SerializerProvider $serializer,
        CacheHandler $cache,
        ExchangeRateRepository $exchangeRateRepository
    )
    {
        $this->client = $client;
        $this->serializer = $serializer->create();
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

        return $this->ratesDeserialize($response->getBody()->getContents());
    }

    public function fetchRatesFromDatabase(string $base, array $targetCurrencies)
    {
        $ratesFromCache = $this->cache->getItem(CacheHandler::RATES_CACHE);
        $currenciesToCalculate = array_merge([$base], $targetCurrencies);

        if ($ratesFromCache instanceof RatesResponse) {
            $currenciesByRates = array_keys($ratesFromCache->getRates());
            $availableRates = array_intersect($currenciesToCalculate, $currenciesByRates);

            if (count($availableRates) === count($currenciesToCalculate)) {
                return $ratesFromCache;
            }
        }

        $rates = $this->exchangeRateRepository->findBy(
            [
                "targetCurrency" => $currenciesToCalculate,
                "baseCurrency" => self::BASE_CURRENCY
            ]
        );
        $ratesArray = [];
        foreach ($rates as $rate) {
            $ratesArray[$rate->getTargetCurrency()] = $rate->getRate();
        }
        $parsedRates = new RatesResponse();
        $parsedRates->setBase(self::BASE_CURRENCY);
        $parsedRates->setRates($ratesArray);

        return $parsedRates;

    }

    private function ratesDeserialize($jsonResponse) : RatesResponse
    {
        return $this->serializer->deserialize($jsonResponse, RatesResponse::class, 'json');
    }
}
