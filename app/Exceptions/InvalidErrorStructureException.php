<?php

namespace App\Exceptions;

use Exception;

class InvalidErrorStructureException extends Exception
{
    public function __construct($message = "Invalid error structure provided")
    {
        parent::__construct($message);
    }
}
