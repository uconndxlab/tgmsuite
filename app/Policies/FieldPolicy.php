<?php

namespace App\Policies;

use App\Models\Field;
use App\Models\User;

class FieldPolicy
{
    public function view(User $user, Field $field): bool
    {
        return $user->is_admin || $field->users()->where('users.id', $user->id)->exists();
    }

    public function update(User $user, Field $field): bool
    {
        return $user->is_admin || $field->users()->where('users.id', $user->id)->exists();
    }

    public function delete(User $user, Field $field): bool
    {
        return $user->is_admin || $field->users()->where('users.id', $user->id)->exists();
    }
}
