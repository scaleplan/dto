<?php
declare(strict_types=1);

namespace Scaleplan\DTO\Exceptions;

/**
 * Class OnlyGettersSupportingException
 *
 * @package Scaleplan\DTO\Exceptions
 */
class OnlyGettersSupportingException extends DTOException
{
    public const MESSAGE = 'dto.only-getters-supported';
    public const CODE = 406;
}
