<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordMatchValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var App\Validator\Constraints\PasswordMatch $constraint */

        if ($value['plainPassword'] !== $value['plainPasswordConfirm']) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}