<?php

namespace App\Validator;


use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class ProductValidator
{
    public function validate($data)
    {
        $validator = Validation::createValidator();
        $constraint = new Assert\Collection([
            'name' => [
                new Assert\Type('string'),
                new Assert\NotBlank(),
                new Assert\Length(['max' => 255])
            ],
            'category' => [
                new Assert\Type('string'),
                new Assert\NotBlank(),
                new Assert\Length(['max' => 255])
            ],
            'price' => [
                new Assert\Type('numeric'),
                new Assert\NotBlank(),
                new Assert\GreaterThanOrEqual(0)
            ],
            'stock' => [
                new Assert\Type('integer'),
                new Assert\NotBlank(),
                new Assert\GreaterThanOrEqual(0)
            ]
        ]);

        return $validator->validate($data, $constraint);
    }
}