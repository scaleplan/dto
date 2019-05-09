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
            if (\array_key_exists($name, $this->toFullArray())) {
                $propertyName = $name;
            }

            if ($propertyName === null
                && \array_key_exists(NameConverter::snakeCaseToLowerCamelCase($name), $this->toFullCamelArray())) {
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
        $rawArray = $this->toFullArray();
        foreach ($rawArray as $key => $value) {
            if (null === $value && !\in_array($key, $this->attributes, true)) {
                unset($rawArray[$key]);
            }
        }

        return array_diff_key($rawArray, get_object_vars($this));
    }

    /**
     * @return array
     */
    public function toFullArray() : array
    {
        $rawArray = (array)$this;
        $keys = array_map(static function ($key) {
            $replaces = [static::class => '', '*' => ''];
            foreach (class_parents(static::class) as $parent) {
                $replaces[$parent] = '';
            }
            return trim(strtr($key, $replaces));
        }, array_keys($rawArray));

        return array_combine($keys, $rawArray);
    }

    /**
     * @param array $rawArray
     *
     * @return array
     */
    protected static function getSnakeArray(array $rawArray) : array
    {
        $keys = array_map(static function ($item) {
            return NameConverter::camelCaseToSnakeCase($item);
        }, array_keys($rawArray));

        return array_combine($keys, $rawArray);
    }

    /**
     * @param array $rawArray
     *
     * @return array
     */
    protected static function getCamelArray(array $rawArray) : array
    {
        $keys = array_map(static function ($key) {
            return NameConverter::snakeCaseToLowerCamelCase($key);
        }, array_keys($rawArray));

        return array_combine($keys, $rawArray);
    }

    /**
     * @return array
     */
    public function toFullSnakeArray() : array
    {
        return static::getSnakeArray($this->toFullArray());
    }

    /**
     * @return array
     */
    public function toFullCamelArray() : array
    {
        return static::getCamelArray($this->toFullArray());
    }

    /**
     * @return array
     */
    public function toSnakeArray() : array
    {
        return static::getSnakeArray($this->toArray());
    }

    /**
     * @return array
     */
    public function toCamelArray() : array
    {
        return static::getCamelArray($this->toArray());
    }
}
