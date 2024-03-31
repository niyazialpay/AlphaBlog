<?php

namespace App\Policies;

use App\Models\PersonalNotes\PersonalNoteCategories;
use App\Models\User;

class PersonalNoteCategoryPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function own(User $user, PersonalNoteCategories $note): bool
    {
        return $user->id == $note->user_id;
    }

    public function create(): bool
    {
        return true;
    }
}
