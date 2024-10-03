<?php

namespace App\Dtos;

abstract class BaseDto
{
    public function toArray()
    {
        return get_object_vars($this);
    }
}
