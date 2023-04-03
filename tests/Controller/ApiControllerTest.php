<?php

namespace App\Tests\Controller;

use App\Controller\ApiController;
use App\Service\Calculator\ExchangeRatesCalculator;
use App\Service\Provider\RatesProvider;
use App\Service\Validator\CurrencyValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validation;

class ApiControllerTest extends WebTestCase
{
    private $containerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->containerMock = $this->createMock(ContainerInterface::class);

        $this->containerMock->expects($this->once())
            ->method("has")
            ->with($this->equalTo('serializer'))
            ->will($this->returnValue(false));
    }

    public function testGetExchangeRatesValidInput()
    {
        $currencyValidator = $this->createMock(CurrencyValidator::class);
        $ratesProvider = $this->createMock(RatesProvider::class);
        $exchangeRatesCalculator = $this->createMock(ExchangeRatesCalculator::class);

        $currencyValidator->method('validateCurrencies')->willReturn(['hasError' => false, 'message' => '']);
        $ratesProvider->method('fetchRatesFromDatabase')->willReturn(['USD' => 1.0, 'EUR' => 0.8]);
        $exchangeRatesCalculator->method('calculateRates')->willReturn(['EUR' => 0.9]);

        $apiController = new ApiController($currencyValidator, $ratesProvider, $exchangeRatesCalculator);
        $apiController->setContainer($this->containerMock);

        $request = Request::create('/api/exchange-rates', 'GET', ['base_currency' => 'USD', 'target_currencies' => 'EUR']);

        $response = $apiController->getExchangeRates($request);

        $this->assertSame(200, $response->getStatusCode());

        $this->assertJsonStringEqualsJsonString(
            '{"base_currency": "USD", "target_currencies": {"EUR": 0.9}}',
            $response->getContent()
        );
    }


    public function testGetExchangeRatesInvalidInputs()
    {
        $currencyValidator = $this->createMock(CurrencyValidator::class);
        $ratesProvider = $this->createMock(RatesProvider::class);
        $exchangeRatesCalculator = $this->createMock(ExchangeRatesCalculator::class);

        $currencyValidator->method('validateCurrencies')->willReturn([
            'hasError' => true,
            'message' => 'Invalid currency: invalid: This value is not a valid currency.'
        ]);

        $apiController = new ApiController($currencyValidator, $ratesProvider, $exchangeRatesCalculator);
        $apiController->setContainer($this->containerMock);

        $request = Request::create('/api/exchange-rates', 'GET', ['base_currency' => 'invalid']);
        $response = $apiController->getExchangeRates($request);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('{"message":"Invalid currency: invalid: This value is not a valid currency."}', $response->getContent());
    }

    public function testGetExchangeRatesInputsWithoutResults()
    {
        $currencyValidator = $this->createMock(CurrencyValidator::class);
        $ratesProvider = $this->createMock(RatesProvider::class);
        $exchangeRatesCalculator = $this->createMock(ExchangeRatesCalculator::class);

        $currencyValidator->method('validateCurrencies')->willReturn([
            'hasError' => false,
            'message' => ''
        ]);

        $ratesProvider->method('fetchRatesFromDatabase')->willReturn(['USD' => 1.0, 'EUR' => 0.8]);
        $exchangeRatesCalculator->method('calculateRates')->willReturn(null);

        $apiController = new ApiController($currencyValidator, $ratesProvider, $exchangeRatesCalculator);
        $apiController->setContainer($this->containerMock);

        $request = Request::create('/api/exchange-rates', 'GET', ['base_currency' => 'COP']);
        $response = $apiController->getExchangeRates($request);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('{"message":"Unable to find provided base currency: COP"}', $response->getContent());

    }

    private function getExchangeRatesControllerInstance()
    {
        $this->ratesProvider->method('fetchRatesFromDatabase')->willReturn([
            'USD' => 1.20,
            'GBP' => 0.85,
        ]);

        $calculator = new ExchangeRatesCalculator();
        $validator = $this->currencyValidator;

        return new ApiController($validator, $this->ratesProvider, $calculator);
    }

}