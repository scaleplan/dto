<?php

namespace Scaleplan\DTO;

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
                    'field' => NameConverter::camelCaseToSnakeCase($err->getPropertyPath()),
                    'message' => $err->getMessage(),
                ];
            }

            throw new ValidationException($msgErrors);
        }

        return true;
    }
}