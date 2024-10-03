<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class PromptInteractionsNotFoundException extends Exception
{
    protected $message = 'Prompt interactions with the given id was not found.';
    protected $code = Response::HTTP_NOT_FOUND;

    public function __construct()
    {
        parent::__construct($this->message);
    }
}
