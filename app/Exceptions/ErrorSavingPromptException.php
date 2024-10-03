<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ErrorSavingPromptException extends Exception
{
    protected $message = 'Error saving user prompt to the database. Please try again later.';
    protected $code = Response::HTTP_BAD_REQUEST;

    public function __construct()
    {
        parent::__construct($this->message);
    }
}
