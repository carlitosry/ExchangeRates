<?php

namespace App\Tests;

use App\Command\ExchangeRatesCommand;
use App\Model\RatesResponse;
use App\Repository\ExchangeRateRepository;
use App\Service\Handler\CacheHandler;
use App\Service\Provider\RatesProvider;
use App\Service\Validator\CurrencyValidator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;


class ExchangeRatesCommandTest extends KernelTestCase
{
    private ExchangeRatesCommand $command;
    private CommandTester $commandTester;
    private MockObject $entityManager;
    private MockObject $ratesProvider;
    private MockObject $cache;
    private MockObject $validator;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->ratesProvider = $this->createMock(RatesProvider::class);
        $this->cache = $this->createMock(CacheHandler::class);
        $this->validator = $this->createMock(CurrencyValidator::class);

        $this->command = new ExchangeRatesCommand(
            $this->ratesProvider,
            $this->entityManager,
            $this->cache,
            $this->validator
        );

        $this->commandTester = new CommandTester($this->command);

    }

    public function testConfigure()
    {
        $this->assertEquals('app:exchange:rates', $this->command->getName());
        $this->assertEquals(
            'Fetches exchange rates from API and stores them in database and Redis.',
            $this->command->getDescription()
        );

        $definition = $this->command->getDefinition();

        $this->assertTrue($definition->hasArgument('base_currency'));
        $this->assertTrue($definition->hasArgument('target_currencies'));

        $this->assertTrue($definition->getArgument('base_currency')->isRequired());
        $this->assertTrue($definition->getArgument('target_currencies')->isArray());
    }


    public function testExecuteWithInvalidBaseCurrency(): void
    {
        // Arrange
        $this->validator->method('validate')
            ->willReturn([0]);

        // ACT
        $this->commandTester->execute([
            'base_currency' => 'INVALID',
            'target_currencies' => ['EUR', 'USD'],
        ]);

        // Assert
        $this->assertStringContainsString(
            "Argument 'INVALID' is not valid",
            $this->commandTester->getDisplay()
        );

    }

    public function testExecuteWithValidInputs()
    {
        // Arrange
        $input = new ArrayInput([
            'base_currency' => 'USD',
            'target_currencies' => ['EUR', 'GBP']
        ]);

        $output = new BufferedOutput();

        $ratesArray = ['EUR' => 1.234, 'GBP' => 1.567];

        $this->validator
            ->method('validate')
            ->willReturn([]);

        $apiResponse = $this->getFakeRateResponse();
        $apiResponse->setRates($ratesArray);

        $this->ratesProvider->expects($this->once())
            ->method('getRates')
            ->willReturn($apiResponse);

        $repository = $this->createMock(ExchangeRateRepository::class);

        $repository->expects($this->once())
            ->method('updateRates')
            ->with('USD', $ratesArray);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);

        $this->cache->expects($this->once())
            ->method('setItem')
            ->with(CacheHandler::RATES_CACHE, $ratesArray);

        // Act
        $this->command->run($input, $output);

        // Assert
        $this->assertStringContainsString(
            "Exchange rates fetched and processed successfully!!",
            $output->fetch()
        );
    }

    public function testExecuteWithInvalidApiResponse()
    {
        // Arrange
        $input = new ArrayInput([
            'base_currency' => 'USD',
            'target_currencies' => ['EUR', 'GBP']
        ]);
        $output = new BufferedOutput();

        $this->validator
            ->method('validate')
            ->willReturn([]);

        $apiResponse = $this->getFakeRateResponse();
        $apiResponse->setSuccess(false);

        $this->ratesProvider->expects($this->once())
            ->method('getRates')
            ->willReturn($apiResponse);

        // Act
        $result = $this->command->run($input, $output);

        // Assert
        $this->assertStringContainsString(
            "The provider has not been successfully",
            $output->fetch()
        );

        $this->assertSame(Command::FAILURE, $result);
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
