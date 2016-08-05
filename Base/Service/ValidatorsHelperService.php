<?php

namespace Araneum\Base\Service;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

/**
 * Class ValidatorsHelperService
 *
 * @package Araneum\Base\Service
 */
class ValidatorsHelperService
{
    /**
     * @var array
     */
    protected $defaultContraintsNames = [
        'NotBlank',
        'Blank',
        'NotNull',
        'IsNull',
        'IsTrue',
        'IsFalse',
        'Email',
        'Url',
        'Ip',
        'Uuid',
        'Date',
        'DateTime',
        'Time',
        'File',
        'Image',
        'Bic',
        'Currency',
        'Luhn',
        'Iban',
        'Isbn',
        'Issn',
    ];

    /**
     * @var array
     */
    protected $specialConstraints = [
        'Callback',
        'Valid',
        'EqualTo',
        'NotEqualTo',
        'IdenticalTo',
        'NotIdenticalTo',
        'LessThan',
        'LessThanOrEqual',
        'GreaterThan',
        'GreaterThanOrEqual',
        'Range',
        'Regex',
        'Length',
        'Type',
        'Choice',
        'Collection',
        'Count',
        'UniqueEntity',
        'Language',
        'Locale',
        'Country',
        'CardScheme',
        'Expression',
        'All',
        'UserPassword',
    ];

    /**
     * @var string
     */
    protected $defaultContraintsNameSpace = 'Symfony\\Component\\Validator\\Constraints\\';

    /**
     * ValidatorsHelperService constructor.
     */
    public function __construct()
    {
        $this->validatorsArray = [];
        foreach ($this->defaultContraintsNames as $contraintsName) {
            $contraintClass = new \ReflectionClass($this->defaultContraintsNameSpace.$contraintsName);
            $this->validatorsArray[$contraintsName] = $contraintClass->newInstance();
        }
    }

    /**
     * @return array
     */
    public function getValidators()
    {
        return $this->validatorsArray;
    }

    /**
     * @return array
     */
    public function generateValidatorsAsAssociativeArray()
    {
        $array = [];
        foreach ($this->defaultContraintsNames as $name) {
            $array[$name] = $name;
        }

        return $array;
    }

    /**
     * @param array $input
     * @param array $constraint
     *
     * @return array | bool
     */
    public function validate($input, $constraint)
    {
        $errors = [];
        foreach ($constraint as $key => $fieldConstraint) {
            $constraintValidations = $this->createValidator()->validate([$key => $input[$key]], $fieldConstraint);

            if (!empty($constraintValidations)) {
                $fieldErrors = $this->getErrors($constraintValidations);
                if (!empty($fieldErrors)) {
                    $errors[] = $fieldErrors;
                }
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }

    /**
     * @return \Symfony\Component\Validator\ValidatorInterface
     */
    private function createValidator()
    {
        return Validation::createValidator();
    }

    /**
     * @param ConstraintViolationListInterface $constraintViolations
     *
     * @return array
     */
    private function getErrors($constraintViolations)
    {
        $errors = [];
        if (!empty($constraintViolations)) {
            foreach ($constraintViolations as $validation) {
                $errors[$validation->getPropertyPath()] = [];
                $errors[$validation->getPropertyPath()][] = $validation->getMessage();
            }
        }

        return $errors;
    }
}
