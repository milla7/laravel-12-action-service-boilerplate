<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\MessageBag;

class ValidationErrorException extends Exception
{
    protected $errors;
    protected $error_code;

    public function __construct($message = "Validation error", $error_code, $errors, $code = 422)
    {
        /**
         * If the errors are an instance of MessageBag, convert it to an array
         */
        if($errors instanceof MessageBag) {
            $errors = $errors->toArray();
        }
        $this->errors = $errors;
        $this->error_code = $error_code;
        parent::__construct($message, $code);
    }

    public function getErrors()
    {
        return [
            'error_code' => $this->error_code ?? 'VALIDATION_ERROR',
            'errors' => $this->errors
        ];
    }

    public function errors()
    {
        return $this->errors;
    }


    public function getErrorCode()
    {
        return $this->error_code;
    }
}
