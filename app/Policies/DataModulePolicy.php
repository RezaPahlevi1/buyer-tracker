<?php

namespace App\Policies;

use App\Models\User;

class DataModulePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // superadmin & sales sama-sama boleh lihat/cari
    }

    public function view(User $user, $model): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isSuperadmin();
    }

    public function update(User $user, $model): bool
    {
        return $user->isSuperadmin();
    }

    public function delete(User $user, $model): bool
    {
        return $user->isSuperadmin();
    }
}