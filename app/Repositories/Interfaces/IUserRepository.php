<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface IUserRepository
{
    public function userExists(int $id): bool;
}
