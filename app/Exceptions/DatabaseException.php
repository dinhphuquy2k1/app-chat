<?php

namespace App\Exceptions;

use Exception;

class DatabaseException extends Exception {

    public function __construct($message = null, $code = 400, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
