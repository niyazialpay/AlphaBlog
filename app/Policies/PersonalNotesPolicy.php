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

    public function own(User $user, PersonalNotes $notes): bool
    {
        return $user->id == $notes->user_id;
    }

}
