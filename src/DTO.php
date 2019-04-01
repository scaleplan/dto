<?php

namespace Scaleplan\DTO;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Scaleplan\DTO\Exceptions\ValidationException;
use Scaleplan\Helpers\NameConverter;
use Scaleplan\InitTrait\InitTrait;
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
    use InitTrait;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * DTO constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->initObject($data);
    }

    /**
     * @return ValidatorInterface
     */
    protected function getValidator() : ValidatorInterface
    {
        static $validator;
        if ($validator) {
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
        $errors = $this->validator->validate($this, null, $groups);
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
        return $this->toSnakeArray();
    }

    /**
     * @return array
     */
    public function toSnakeArray() : array
    {
        $rawArray = (array)$this;
        $keys = array_map(function ($item) {
            return NameConverter::camelCaseToSnakeCase(trim(strtr($item, [__CLASS__ => '', '*' => ''])));
        }, array_keys($rawArray));

        return array_combine($keys, $rawArray);
    }

    /**
     * @return array
     */
    public function toCamelArray() : array
    {
        $rawArray = (array)$this;
        $keys = array_map(function ($item) {
            return NameConverter::snakeCaseToLowerCamelCase(trim(strtr($item, [__CLASS__ => '', '*' => ''])));
        }, array_keys($rawArray));

        return array_combine($keys, $rawArray);
    }
}
