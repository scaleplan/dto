<?php

namespace Scaleplan\DTO;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Scaleplan\DTO\Exceptions\ValidationException;
use Scaleplan\Helpers\NameConverter;
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
    private $attributes = [];

    /**
     * DTO constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        foreach ($data as $name => &$value) {
            $propertyName = null;
            if (property_exists($this, $name)) {
                $propertyName = $name;
            }

            if ($propertyName === null
                && property_exists($this, NameConverter::snakeCaseToLowerCamelCase($name))) {
                $propertyName = NameConverter::snakeCaseToLowerCamelCase($name);
            }

            if (!$propertyName) {
                continue;
            }

            $this->attributes[] = $propertyName;

            $methodName = 'set' . ucfirst($propertyName);
            if (is_callable([$this, $methodName])) {
                $this->{$methodName}($value);
                continue;
            }

            if (array_key_exists($propertyName, get_object_vars($this))) {
                $this->{$propertyName} = $value;
            }
        }
        unset($value);
    }

    /**
     * @return ValidatorInterface
     */
    protected function getValidator() : ValidatorInterface
    {
        static $validator;
        if (!$validator) {
            AnnotationRegistry::registerLoader('class_exists');

            $validator = Validation::createValidatorBuilder()
                ->enableAnnotationMapping()
                ->getValidator();
        }

        return $validator;
    }

    /**
     * @param array|null $groups
     *
     * @throws ValidationException
     */
    public function validate(array $groups = null) : void
    {
        /** @var ConstraintViolationList|ConstraintViolation[] $errors */
        $errors = $this->getValidator()->validate($this, null, $groups);
        if (count($errors) > 0) {
            $msgErrors = [];
            foreach ($errors as $err) {
                $msgErrors[] = [
                    'field'   => NameConverter::camelCaseToSnakeCase($err->getPropertyPath()),
                    'message' => $err->getMessage(),
                ];
            }

            throw new ValidationException($msgErrors);
        }
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return $this->getRawArray();
    }

    /**
     * @return array
     */
    protected function getRawArray() : array
    {
        $rawArray = (array)$this;
        foreach ($rawArray as $key => $value) {
            $newKey = trim(strtr($key, [static::class => '', '*' => '', __CLASS__ => '']));
            unset($rawArray[$key]);
            if (null !== $value || \in_array($newKey, $this->attributes, true)) {
                $rawArray[$newKey] = $value;
            }
        }

        return array_diff_key($rawArray, get_object_vars($this));
    }

    /**
     * @return array
     */
    public function toSnakeArray() : array
    {
        $rawArray = $this->getRawArray();
        $keys = array_map(static function ($item) {
            return NameConverter::camelCaseToSnakeCase($item);
        }, array_keys($rawArray));

        return array_combine($keys, $rawArray);
    }

    /**
     * @return array
     */
    public function toCamelArray() : array
    {
        $rawArray = $this->getRawArray();
        $keys = array_map(static function ($item) {
            return NameConverter::snakeCaseToLowerCamelCase($item);
        }, array_keys($rawArray));

        return array_combine($keys, $rawArray);
    }
}
