<?php

namespace Scaleplan\DTO;

use Scaleplan\DTO\Exceptions\ValidationException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class DTO
 *
 * @package Scaleplan\DTO
 */
class DTO
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @param string $snake
     *
     * @return string
     */
    protected static function snakeCaseToLowerCamelCase(string $snake) : string
    {
        return lcfirst(
            str_replace(' ', '', ucwords(str_replace('_', ' ', $snake)))
        );
    }

    /**
     * @param string $snake
     *
     * @return string
     */
    protected static function snakeCaseToCamelCase(string $snake) : string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $snake)));
    }

    /**
     * @param string $camel
     *
     * @return string
     */
    protected static function camelCaseToSnakeCase(string $camel)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $camel));
    }

    /**
     * DTO constructor.
     *
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        foreach ($request->getParams() as $name => $value) {
            $methodName = 'set' . static::snakeCaseToCamelCase($name);
            if (method_exists($this, $methodName)) {
                $this->{$methodName}($value);
                continue;
            }

            $propertyName = static::snakeCaseToLowerCamelCase($name);
            if (property_exists($this, $propertyName)) {
                $this->{$propertyName} = $value;
            }
        }

        $this->validator = Validation::createValidator();
    }

    /**
     * @return bool
     *
     * @throws ValidationException
     */
    public function validate()
    {
        /** @var ConstraintViolationList|ConstraintViolation[] $errors */
        $errors = $this->validator->validate($this);
        if (count($errors) > 0) {
            $msgErrors = [];
            foreach ($errors as $err) {
                $msgErrors[] = [
                    'field' => static::camelCaseToSnakeCase($err->getPropertyPath()),
                    'message' => $err->getMessage(),
                ];
            }

            throw new ValidationException($msgErrors);
        }

        return true;
    }
}