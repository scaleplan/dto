<?php

namespace Scaleplan\DTO\Exceptions;

/**
 * Class ValidationException
 *
 * @package Scaleplan\DTO\Exceptions
 */
class ValidationException extends DTOException
{
    public const MESSAGE = 'Ошибка валидации.';
    public const CODE = 422;

    /**
     * @var array
     */
    private $errors;

    /**
     * ValidationException constructor.
     *
     * @param array $errors
     * @param string $message
     * @param int $code
     */
    public function __construct(array $errors = [], ?string $message = '', int $code = 0)
    {
        $this->errors = $errors;

        parent::__construct($message ?: static::MESSAGE, $code ?: static::CODE);
    }

    /**
     * @return array
     */
    public function getErrors() : array
    {
        return $this->errors;
    }
}
