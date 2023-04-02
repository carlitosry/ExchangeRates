<?php

namespace App\Service\Calculator;

use App\Model\RatesResponse;
use App\Repository\ExchangeRateRepository;
use App\Service\Handler\CacheHandler;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * class ExchangeRatesCalculator
 *
 * @package    ExchangeRatesCalculator
 * @copyright  2023 carlitosry <reyes.syscom@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ExchangeRatesCalculator
{
    public function calculateRates(string $baseCurrency, array $targetCurrencies, RatesResponse $ratesResponse): ?array
    {
        if (!isset($ratesResponse->getRates()[$baseCurrency])) {
            return null;
        }

        $ratesResult = [];
        $rates = $ratesResponse->getRates();
        foreach ($targetCurrencies as $targetCurrency) {
            $ratesResult[$targetCurrency] = $rates[$targetCurrency] / $rates[$baseCurrency];
        }

        return $ratesResult;
    }

}
