<?php

namespace Scaleplan\DTO\Exceptions;

/**
 * Class ValidationException
 *
 * @package Scaleplan\DTO\Exceptions
 */
class ValidationException extends DTOException
{
    public const MESSAGE = 'Validation error.';

    /**
     * @var array
     */
    private $errors;

    /**
     * ValidationException constructor.
     *
     * @param array $errors
     * @param string|null $message
     */
    public function __construct(array $errors = [], ?string $message = NULL)
    {
        $this->errors = $errors;

        parent::__construct($message ?? static::MESSAGE);
    }

    /**
     * @return array
     */
    public function getErrors() : array
    {
        return $this->errors;
    }
}
