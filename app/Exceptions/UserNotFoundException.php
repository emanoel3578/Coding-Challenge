<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class UserNotFoundException extends Exception
{
    protected $message = 'User not found.';
    protected $code = Response::HTTP_NOT_FOUND;

    public function __construct()
    {
        parent::__construct($this->message);
    }
}
