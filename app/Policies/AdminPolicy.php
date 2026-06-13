<?php

declare(strict_types=1);

namespace App\Policies;

class AdminPolicy
{
    public function manageUsers(?array $user): bool
    {
        return $this->can($user, 'manage-users');
    }

    public function manageSettings(?array $user): bool
    {
        return $this->can($user, 'manage-settings');
    }

    public function manageContent(?array $user): bool
    {
        return $this->can($user, 'manage-pages');
    }

    private function can(?array $user, string $permission): bool
    {
        if (! is_array($user) || ! isset($user['id'])) {
            return false;
        }

        return (bool) can($permission);
    }
}
