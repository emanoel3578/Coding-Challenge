<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\IUserRepository;

class UserRepository implements IUserRepository
{
    private User $model;

    public function __construct()
    {
        $this->model = new User();
    }

    public function userExists(int $id): bool
    {
        $user = $this->model->find($id)->first();

        return $user !== null;
    }
}
