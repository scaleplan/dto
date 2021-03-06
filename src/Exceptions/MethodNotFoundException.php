<?php

namespace Scaleplan\DTO\Exceptions;

/**
 * Class MethodNotFoundException
 *
 * @package Scaleplan\DTO\Exceptions
 */
class MethodNotFoundException extends DTOException
{
    public const MESSAGE = 'dto.method-not-found';
}
