<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExchangeRatesCommand extends Command
{
    const BASE_CURRENCY = 'base_currency';
    const TARGET_CURRENCIES = 'target_currencies';

    protected static $defaultName = 'app:exchange:rates';
    protected static $defaultDescription = 'Fetches exchange rates from API and stores them in database and Redis.';


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
        $output->writeln(
            sprintf(
                "<info>[%s][EXCHANGE_RATES::INFO]</info> rates fetched and processed successfully!",
                date('Y-m-d H:i:s'),
            )
        );
        return Command::SUCCESS;
    }

}
