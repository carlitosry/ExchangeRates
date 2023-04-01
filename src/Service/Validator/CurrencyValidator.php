<?php


namespace App\Service\Validator;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * CurrencyValidator
 *
 * @package    CurrencyValidator
 * @copyright  2023 Carlitosry <reyes.syscom@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CurrencyValidator
{
    const BTC_CURRENCY = 'BTC';

    private ValidatorInterface $validator;

    public function __construct(
        ValidatorInterface $validator
    )
    {
        $this->validator = $validator;
    }

    /**
     * @param array $currencies
     * @return ConstraintViolationListInterface[]
     */
    public function validateCurrencyArguments(array $currencies) : array
    {
        $errorsArray = [];
        foreach ($currencies as $currency) {
            if ($currency === self::BTC_CURRENCY) {
                continue;
            }

            $errors = $this->validator->validate(
                $currency,
                [
                    new Assert\NotBlank(),
                    new Assert\Currency()
                ]
            );

            if ($errors->count() > 0) {
                $errorsArray[] = $errors;
            }
        }

        return $errorsArray;
    }

    /**
     * @param string[] $currencies
     * @return array
     */
    public function validateCurrencies(array $currencies) : array
    {
        $errors = $this->validateCurrencyArguments($currencies);

        $firstError = null;
        $result = [
            'hasError' => false,
            'message' => 'No Errors'
        ];

        if (empty($errors)) {
            return $result;
        }

        foreach ($errors as $errorList) {
            if ($errorList->count() > 0) {
                $firstError = $errorList->get(0);
                break;
            }
        }

        if ($firstError !== null) {
            $result['hasError'] = true;
            $result['message'] = sprintf(
                "Invalid currency: %s: %s",
                $firstError->getInvalidValue(),
                $firstError->getMessage()
            );
        }

        return $result;
    }

}
