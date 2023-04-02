<?php

namespace App\Tests\Service\Provider;

use App\Model\RatesResponse;
use App\Repository\ExchangeRateRepository;
use App\Service\Handler\CacheHandler;
use App\Service\Provider\RatesProvider;
use App\Service\Provider\SerializerProvider;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

class RatesProviderTest extends TestCase
{
    private MockObject $client;
    private MockObject $serializerProvider;
    private MockObject $serializer;
    private MockObject $cacheHandler;
    private MockObject $exchangeRateRepository;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->serializerProvider = $this->createMock(SerializerProvider::class);
        $this->exchangeRateRepository = $this->createMock(ExchangeRateRepository::class);
        $this->cacheHandler = $this->createMock(CacheHandler::class);
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetRatesSuccess()
    {
        $jsonResponse = '{"success": true, "base": "USD", "rates": {"EUR": 0.825, "GBP": 0.708, "JPY": 108.78}}';

        $this->serializerProvider->expects($this->once())
            ->method('create')
            ->willReturn(
                $this->serializer
            );

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($jsonResponse, RatesResponse::class, 'json')
            ->willReturn(
                $this->getFakeRateResponse("USD", ['EUR' => 0.825, 'GBP' => 0.708, 'JPY' => 108.78])
            );

        $this->client->expects($this->once())
            ->method('request')
            ->willReturn(
                new Response(200, [], $jsonResponse)
            );

        $ratesProvider = new RatesProvider(
            $this->client,
            $this->serializerProvider,
            $this->cacheHandler,
            $this->exchangeRateRepository
        );

        $ratesResponse = $ratesProvider->getRates('USD', ['EUR', 'GBP', 'JPY']);

        $this->assertInstanceOf(RatesResponse::class, $ratesResponse);
        $this->assertTrue($ratesResponse->getSuccess());
        $this->assertEquals('USD', $ratesResponse->getBase());
        $this->assertEquals(['EUR' => 0.825, 'GBP' => 0.708, 'JPY' => 108.78], $ratesResponse->getRates());
    }


    public function testGetRatesFail()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->willReturn(new Response(400, [], '{"error": "Bad Request"}'));

        $ratesProvider = new RatesProvider(
            $this->client,
            $this->serializerProvider,
            $this->cacheHandler,
            $this->exchangeRateRepository
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The response status code is not valid');

        $ratesProvider->getRates('USD', ['EUR', 'GBP', 'JPY']);
    }

    private function getFakeRateResponse($base = 'EUR', $ratesArray = ['USD' => 0.99]): RatesResponse
    {
        $rates = new RatesResponse();
        $rates->setBase($base);
        $rates->setRates($ratesArray);
        $rates->setSuccess(true);

        return $rates;
    }
}
