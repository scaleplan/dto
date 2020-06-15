<?php

namespace Scaleplan\DTO\Exceptions;

/**
 * Class PropertyNotFoundException
 *
 * @package Scaleplan\DTO\Exceptions
 */
class PropertyNotFoundException extends DTOException
{
    public const MESSAGE = 'Свойство :property не найдено.';
    public const CODE = 404;

    /**
     * PropertyNotFoundException constructor.
     *
     * @param string $propertyName
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $propertyName, string $message = '', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(
            str_replace(':property', $propertyName, $message ?: static::MESSAGE),
            $code ?: static::CODE,
            $previous
        );
    }
}
