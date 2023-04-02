<?php

namespace App\Tests\Service\Provider;

use App\Model\RatesResponse;
use App\Repository\ExchangeRateRepository;
use App\Service\Handler\CacheHandler;
use App\Service\Provider\RatesProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class RatesProviderTest extends TestCase
{
    private RatesProvider $ratesProvider;
    private Client $client;
    private CacheHandler $cache;
    private ExchangeRateRepository $exchangeRateRepository;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->cache = $this->createMock(CacheHandler::class);
        $this->exchangeRateRepository = $this->createMock(ExchangeRateRepository::class);

        $this->ratesProvider = new RatesProvider(
            $this->client,
            $this->cache,
            $this->exchangeRateRepository
        );
    }

    public function testGetRates(): void
    {
        $baseCurrency = 'EUR';
        $targetCurrencies = ['USD', 'GBP'];

        $jsonResponse = '{"base": "EUR", "success": true, "date": "2023-04-02", "rates": {"USD": 1.23, "GBP": 0.89}}';
        $ratesResponse = new RatesResponse(["USD" => 1.23, "GBP" => 0.89]);
        $ratesResponse->setSuccess(true);
        $ratesResponse->setBase("EUR");
        $ratesResponse->setDate("2023-04-02");

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '', [
                'query' => [
                    'base' => $baseCurrency,
                    'symbols' => implode(',', $targetCurrencies)
                ]
            ])
            ->willReturn(new Response(200, [], $jsonResponse));

        $result = $this->ratesProvider->getRates($baseCurrency, $targetCurrencies);

        $this->assertEquals($ratesResponse->getRates(), $result->getRates());
        $this->assertEquals($ratesResponse->getSuccess(), $result->getSuccess());
        $this->assertEquals($ratesResponse->getDate(), $result->getDate());
        $this->assertEquals($ratesResponse->getBase(), $result->getBase());
    }

    public function testFetchRatesFromDatabase(): void
    {
        // Set up the test case
        $baseCurrency = 'EUR';
        $targetCurrencies = ['USD', 'GBP'];
        $ratesFromDb = [
            ['targetCurrency' => 'USD', 'rate' => 1.2],
            ['targetCurrency' => 'GBP', 'rate' => 0.9],
        ];
        $ratesInDb = [
            'USD' => 1.2,
            'GBP' => 0.9
        ];

        $ratesInCache = [
            'USD' => 1.3,
            'GBP' => 0.8
        ];

        $this->exchangeRateRepository
            ->expects($this->once())
            ->method('findRatesByBaseAndTargetCurrencies')
            ->willReturn($ratesFromDb);
        $this->cache
            ->expects($this->atLeastOnce())
            ->method('updateCurrencyCacheItems')
            ->with($baseCurrency, $ratesInDb);

        $this->cache
            ->expects($this->exactly(3))
            ->method('getItem')
            ->willReturnOnConsecutiveCalls(
                [],
                $targetCurrencies,
                $ratesInCache
            );

        // Call the function for the first time to fetch from the database
        $result = $this->ratesProvider->fetchRatesFromDatabase($baseCurrency, $targetCurrencies);
        $this->assertEquals($ratesInDb, $result);

        // Call the function for the second time to fetch from the cache
        $result = $this->ratesProvider->fetchRatesFromDatabase($baseCurrency, $targetCurrencies);
        $this->assertEquals($ratesInCache, $result);
    }
}
