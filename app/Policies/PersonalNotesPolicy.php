<?php

namespace App\Policies;

use App\Models\PersonalNotes;
use App\Models\User;

class PersonalNotesPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function own(User $user, PersonalNotes $note): bool
    {
        return $user->id == $note->user_id;
    }

    public function create(): bool
    {
        return true;
    }
}
