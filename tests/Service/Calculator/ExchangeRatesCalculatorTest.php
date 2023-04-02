<?php

namespace App\Tests\Service\Calculator;

use App\Model\RatesResponse;
use App\Service\Calculator\ExchangeRatesCalculator;
use PHPUnit\Framework\TestCase;

class ExchangeRatesCalculatorTest extends TestCase
{

    /**
     * @dataProvider calculateRatesDataProvider
     */
    public function testCalculateRates(string $baseCurrency, array $targetCurrencies, RatesResponse $ratesResponse, ?array $expectedRates): void
    {
        $calculator = new ExchangeRatesCalculator();

        // Create a RatesResponse object with some test data
        $ratesResponse = new RatesResponse([
            'EUR' => 1.0,
            'USD' => 1.2,
            'GBP' => 0.9,
            'COP' => 3500,
        ]);

        // Calculate rates for EUR as the base currency and USD and GBP as target currencies
        $baseCurrency = 'USD';
        $targetCurrencies = ['GBP', 'COP'];
        $expectedRates = [
            "GBP" => 0.75,
            "COP" => 2916.666666666667
        ];
        $actualRates = $calculator->calculateRates($baseCurrency, $targetCurrencies, $ratesResponse);

        // Check that the calculated rates match the expected rates
        $this->assertSame($expectedRates, $actualRates);
    }

    public function calculateRatesDataProvider(): array
    {
        return [
            [
                // Test case 1
                'EUR', // Base currency
                ['USD', 'GBP'], // Target currencies
                new RatesResponse([
                    'EUR' => 1.0,
                    'USD' => 1.2,
                    'GBP' => 0.9,
                ]), // Rates response
                [
                    'USD' => 1.2,
                    'GBP' => 0.9,
                ], // Expected result
            ],
            [
                // Test case 2
                'USD',
                ['GBP', 'COP'],
                new RatesResponse([
                    'EUR' => 0.8,
                    'USD' => 1.0,
                    'GBP' => 0.6,
                ]),
                [
                    'GBP' => 0.75,
                    'COP' => 2916.666666666667,
                ],
            ],
            [
                // Test case 3
                'INVALID',
                ['USD', 'GBP'],
                new RatesResponse([
                    'EUR' => 1.0,
                    'USD' => 1.2,
                    'GBP' => 0.9,
                ]),
                null,
            ],
        ];
    }
}