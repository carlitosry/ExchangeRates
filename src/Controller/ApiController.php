<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\Validator\CurrencyValidator;
use App\Service\Calculator\ExchangeRatesCalculator;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Provider\RatesProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ApiController extends AbstractController
{
    private CurrencyValidator $currencyValidator;
    private RatesProvider $ratesProvider;
    private ExchangeRatesCalculator $exchangeRatesCalculator;
    public function __construct(
        CurrencyValidator $currencyValidator,
        RatesProvider $ratesProvider,
        ExchangeRatesCalculator $exchangeRatesCalculator,
    )
    {
        $this->currencyValidator = $currencyValidator;
        $this->ratesProvider = $ratesProvider;
        $this->exchangeRatesCalculator = $exchangeRatesCalculator;

    }

    #[Route('/exchange-rates', methods: ['GET'])]
    public function getExchangeRates(Request $request): JsonResponse
    {
        $baseCurrency = $request->query->get('base_currency');
        $target = $request->query->get('target_currencies', []);

        if (!empty($target)) {
            $target = explode(',', $target);
        }

        $currenciesToCalculate = array_merge([$baseCurrency], $target);
        list('hasError' => $hasError, 'message' => $message) = $this->currencyValidator->validateCurrencies(
            $currenciesToCalculate
        );

        if ($hasError) {
            return $this->json(
                ['message' => $message],
                Response::HTTP_BAD_REQUEST
            );
        }

        $ratesResponse = $this->ratesProvider->fetchRatesFromDatabase($baseCurrency, $target);
        $calculatedRates = $this->exchangeRatesCalculator->calculateRates($baseCurrency, $target, $ratesResponse);

        if (!$calculatedRates) {
            return $this->json(
                ['message' => "Unable to find provided base currency: ".$baseCurrency],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->json([
            'base_currency' => $baseCurrency, 'target_currencies' => $calculatedRates
        ]);
    }
}
