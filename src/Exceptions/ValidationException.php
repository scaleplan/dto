<?php

namespace Scaleplan\DTO\Exceptions;

/**
 * Class ValidationException
 *
 * @package Scaleplan\DTO\Exceptions
 */
class ValidationException extends \Exception
{
    public const MESSAGE = 'Validation error.';

    /**
     * ValidationException constructor.
     *
     * @param string $message
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = "", \Throwable $previous = null)
    {
        parent::__construct($message, 422, $previous);
    }
}