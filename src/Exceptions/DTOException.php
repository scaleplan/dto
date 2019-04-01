<?php

namespace Scaleplan\DTO\Exceptions;

/**
 * Class DTOException
 *
 * @package Scaleplan\DTO\Exceptions
 */
class DTOException extends \Exception
{
    public const MESSAGE = 'DTO error.';

    /**
     * DTOException constructor.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = '', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?: static::MESSAGE, $code, $previous);
    }
}
