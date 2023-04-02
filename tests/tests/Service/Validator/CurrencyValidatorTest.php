<?php

namespace App\Tests\Service\Validator;

use App\Service\Validator\CurrencyValidator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validation;

class CurrencyValidatorTest extends KernelTestCase
{
    private CurrencyValidator $currencyValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->getValidator();

        $this->currencyValidator = new CurrencyValidator($validator);
    }

    public function validateCurrenciesDataProvider(): array
    {
        return [
            'Valid currencies' => [
                ['USD', 'EUR'],
                false,
                'No Errors'
            ],
            'Invalid currency' => [
                ['USD', 'INVALID'],
                true,
                'Invalid currency: INVALID: This value is not a valid currency.'
            ],
            'Blank currency' => [
                ['USD', ''],
                true,
                'Invalid currency: : This value should not be blank.'
            ]
        ];
    }

    /**
     * @dataProvider validateCurrenciesDataProvider
     */
    public function testValidateCurrencies(array $currencies, bool $expectedHasError, string $expectedMessage): void
    {
        $result = $this->currencyValidator->validateCurrencies($currencies);

        $this->assertSame($expectedHasError, $result['hasError']);
        $this->assertSame($expectedMessage, $result['message']);
    }
}