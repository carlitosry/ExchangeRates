<?php

namespace App\Exception;

use InvalidArgumentException;

/**
 * Exception that is thrown when an array of currencies fails validation.
 *
 * @package CurrencyValidationException
 */
class CurrencyValidationException extends InvalidArgumentException
{
    /**
     * Constructs a new CurrencyValidationException.
     *
     * @param string         $message  The error message.
     * @param int            $code     The error code.
     * @param Throwable|null $previous The previous exception.
     */
    public function __construct(string $message, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}