<?php

namespace App\Tests\Service\Calculator;

use App\Service\Calculator\ExchangeRatesCalculator;
use PHPUnit\Framework\TestCase;

class ExchangeRatesCalculatorTest extends TestCase
{

    /**
     * @dataProvider calculateRatesDataProvider
     */
    public function testCalculateRates(string $baseCurrency, array $targetCurrencies, array $rates, ?array $expectedRates): void
    {
        $calculator = new ExchangeRatesCalculator();

        $actualRates = $calculator->calculateRates($baseCurrency, $targetCurrencies, $rates);

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
                [
                    'EUR' => 1.0,
                    'USD' => 1.2,
                    'GBP' => 0.9,
                ], // Rates response
                [
                    'USD' => 1.2,
                    'GBP' => 0.9,
                ], // Expected result
            ],
            [
                // Test case 2
                'USD',
                ['GBP', 'COP'],
                [
                    'EUR' => 1.0,
                    'USD' => 1.2,
                    'GBP' => 0.9,
                    'COP' => 3500,
                ],
                [
                    'GBP' => 0.75,
                    'COP' => 2916.666666666667
                ],
            ],
            [
                // Test case 3
                'INVALID',
                ['USD', 'GBP'],
                [
                    'EUR' => 1.0,
                    'USD' => 1.2,
                    'GBP' => 0.9,
                ],
                null,
            ],
        ];
    }
}