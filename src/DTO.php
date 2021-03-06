<?php
declare(strict_types=1);

namespace Scaleplan\DTO;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Scaleplan\DTO\Exceptions\MethodNotFoundException;
use Scaleplan\DTO\Exceptions\OnlyGettersSupportingException;
use Scaleplan\DTO\Exceptions\PropertyNotFoundException;
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
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var bool
     */
    protected $allowMagicSet = true;

    /**
     * DTO constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $name => &$value) {
            $propertyName = null;
            if (\array_key_exists($name, $this->toFullArray())) {
                $propertyName = $name;
            }

            if ($propertyName === null
                && \array_key_exists(NameConverter::snakeCaseToLowerCamelCase($name), $this->toFullArray())) {
                $propertyName = NameConverter::snakeCaseToLowerCamelCase($name);
            }

            if ($propertyName === null
                && \array_key_exists(NameConverter::snakeCaseToCamelCase($name), $this->toFullArray())) {
                $propertyName = NameConverter::snakeCaseToCamelCase($name);
            }

            if (!$propertyName) {
                continue;
            }

            $this->attributes[] = $propertyName;

            $methodName = 'set' . ucfirst($propertyName);
            if (method_exists($this, $methodName)) {
                $this->{$methodName}($value);
                continue;
            }

            if (property_exists($this, $propertyName)) {
                $this->{$propertyName} = $value;
            }
        }
        unset($value);
    }

    /**
     * @return ValidatorInterface
     * @throws \Symfony\Component\Validator\Exception\LogicException
     * @throws \Symfony\Component\Validator\Exception\ValidatorException
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
     * @throws \Symfony\Component\Validator\Exception\LogicException
     * @throws \Symfony\Component\Validator\Exception\ValidatorException
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
            if (!\in_array($key, $this->attributes, true)) {
                unset($rawArray[$key]);
            }
        }

        return $rawArray;
    }

    /**
     * @return array
     */
    protected function getFullArrayKeys() : array
    {
        $rawArray = (array)$this;
        $replaces = [static::class => '', '*' => ''];
        foreach (class_parents(static::class) as $parent) {
            $replaces[$parent] = '';
        }

        $keysArray = [];
        foreach ($rawArray as $key => $value) {
            $keysArray[] = trim(strtr($key, $replaces));
        }

        return $keysArray;
    }

    /**
     * @return array
     */
    public function toFullArray() : array
    {
        $rawArray = array_combine($this->getFullArrayKeys(), (array)$this);

        unset($rawArray['attributes'], $rawArray['allowMagicSet']);

        $array = [];
        foreach ($rawArray as $property => $value) {
            $methodName = ucfirst($property);
            if (method_exists($this, $methodName)) {
                $array[$property] = $this->$methodName();
                continue;
            }

            $methodName = 'get' . ucfirst($property);
            $array[$property] = is_callable([$this, $methodName]) ? $this->$methodName() : $value;
        }

        return $array;
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

    /**
     * @param string $name
     * @param $value
     *
     * @throws PropertyNotFoundException
     */
    protected function set(string $name, $value) : void
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
            $this->attributes[] = $name;
            return;
        }

        throw new PropertyNotFoundException($name);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    protected function get(string $name)
    {
        if (in_array($name, $this->getFullArrayKeys(), true) !== false) {
            return $this->{$name};
        }

        //throw new PropertyNotFoundException($name);
        return null;
    }

    /**
     * @param string $methodName
     * @param array $args
     *
     * @return bool|mixed
     *
     * @throws MethodNotFoundException
     * @throws OnlyGettersSupportingException
     * @throws PropertyNotFoundException
     */
    public function __call(string $methodName, array $args)
    {
        if (strpos($methodName, 'get') === 0) {
            $name = lcfirst(str_replace('get', '', $methodName));
            return $this->get($name);
        }

        if (strpos($methodName, 'set') === 0) {
            if (!$this->allowMagicSet) {
                throw new OnlyGettersSupportingException();
            }

            $name = lcfirst(str_replace('set', '', $methodName));
            $this->set($name, $value = $args[0]);
            return true;
        }

        throw new MethodNotFoundException("Метод $methodName не найден.");
    }

    /**
     * @param string $propertyName
     */
    public function removeProperty(string $propertyName) : void
    {
        if (in_array($propertyName, $this->getFullArrayKeys(), true) !== false) {
            unset($this->$propertyName);
        }

        if (($key = array_search($propertyName, $this->attributes, true)) !== false) {
            unset($this->attributes[$key]);
        }
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return $this->toFullArray()[$name];
    }
}
