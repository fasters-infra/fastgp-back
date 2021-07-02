<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    private $model;

    function __construct(User $model)
    {
        $this->model = $model;
    }

    public function eletronicPointProfile(int $userId)
    {
        return $this->model->select('eletronic_point_profiles.*')
            ->where('users.id', $userId)
            ->join('eletronic_point_profiles', 'users.eletronic_point_profile_id', '=', 'eletronic_point_profiles.id')
            ->first();
    }
}
