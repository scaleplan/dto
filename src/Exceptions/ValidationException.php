<?php

namespace Scaleplan\DTO\Exceptions;

use function Scaleplan\Translator\translate;

/**
 * Class ValidationException
 *
 * @package Scaleplan\DTO\Exceptions
 */
class ValidationException extends DTOException
{
    public const MESSAGE = 'dto.validation-error';
    public const CODE    = 422;

    /**
     * @var array
     */
    private $errors;

    /**
     * ValidationException constructor.
     *
     * @param array $errors
     * @param string|null $message
     * @param int $code
     *
     * @throws \ReflectionException
     * @throws \Scaleplan\DependencyInjection\Exceptions\ContainerTypeNotSupportingException
     * @throws \Scaleplan\DependencyInjection\Exceptions\DependencyInjectionException
     * @throws \Scaleplan\DependencyInjection\Exceptions\ParameterMustBeInterfaceNameOrClassNameException
     * @throws \Scaleplan\DependencyInjection\Exceptions\ReturnTypeMustImplementsInterfaceException
     */
    public function __construct(array $errors = [], ?string $message = '', int $code = 0)
    {
        $this->errors = $errors;

        parent::__construct($message ?: translate(static::MESSAGE) ?: static::MESSAGE, $code ?: static::CODE);
    }

    /**
     * @return array
     */
    public function getErrors() : array
    {
        return $this->errors;
    }
}
