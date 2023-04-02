<?php

namespace App\Command;

use App\Entity\ExchangeRate;
use App\Repository\ExchangeRateRepository;
use App\Service\Handler\CacheHandler;
use App\Service\Provider\RatesProvider;
use App\Service\Validator\CurrencyValidator;
use App\Exception\CurrencyValidationException;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;


class ExchangeRatesCommand extends Command
{
    const BASE_CURRENCY = 'base_currency';
    const TARGET_CURRENCIES = 'target_currencies';

    protected static $defaultName = 'app:exchange:rates';
    protected static $defaultDescription = 'Fetches exchange rates from API and stores them in database and Redis.';

    private EntityManagerInterface $entityManager;
    private RatesProvider $ratesProvider;
    private CacheHandler $cache;
    private CurrencyValidator $validator;


    public function __construct(
        RatesProvider $ratesProvider,
        EntityManagerInterface $entityManager,
        CacheHandler $cache,
        CurrencyValidator $validator
    )
    {
        $this->ratesProvider = $ratesProvider;
        $this->entityManager = $entityManager;
        $this->cache = $cache;
        $this->validator = $validator;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument(
                self::BASE_CURRENCY,
                InputArgument::REQUIRED,
                'The base currency'
            )
            ->addArgument(
                self::TARGET_CURRENCIES,
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'The target currencies'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Define the base currency and target currencies
        $baseCurrency = $input->getArgument(self::BASE_CURRENCY);
        $targetCurrencies = $input->getArgument(self::TARGET_CURRENCIES);

        try {

            list('hasError' => $hasError, 'message' => $message) = $this->validator->validateCurrencies(
                array_merge([$baseCurrency], $targetCurrencies)
            );

            if ($hasError) {
                throw new CurrencyValidationException($message);
            }

            $ratesResponse = $this->ratesProvider->getRates(
                $baseCurrency,
                $targetCurrencies
            );

            if (!$ratesResponse->getSuccess()) {
                throw new LogicException('The provider response has not been successfully');
            }

            /** @var ExchangeRateRepository $exchangeRateRepository */
            $exchangeRateRepository = $this->entityManager->getRepository(ExchangeRate::class);

            $exchangeRateRepository->updateRates($baseCurrency, $ratesResponse->getRates());
            $this->cache->updateCurrencyCacheItems($baseCurrency, $ratesResponse->getRates());

        } catch (GuzzleException|InvalidArgumentException|LogicException|CurrencyValidationException $e) {
            $output->writeln(
                sprintf(
                    "<error>[%s][EXCHANGE_RATES::ERROR]</error> rates could not fetch: %s",
                    date('Y-m-d H:i:s'),
                    $e->getMessage()
                )
            );
            return Command::FAILURE;
        }

        $output->writeln(
            sprintf(
                "<info>[%s][EXCHANGE_RATES::INFO]</info> rates fetched and processed successfully!",
                date('Y-m-d H:i:s'),
            )
        );
        return Command::SUCCESS;
    }

}
